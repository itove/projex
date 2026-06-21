<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Org;
use App\Entity\Project;
use App\Entity\ProjectSubtype;
use App\Entity\ProjectType;
use App\Entity\User;
use App\Enum\FundingSource;
use App\Enum\ProjectNature;
use App\Enum\ProjectStatus;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Overtrue\Pinyin\Pinyin;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ProjectSpreadsheetImportService
{
    /** @var array<string, ProjectType> */
    private array $importTypesByNormalizedName = [];

    /** @var array<string, ProjectSubtype> */
    private array $importSubtypesByCacheKey = [];

    /** @var list<ProjectType> */
    private array $importPendingTypes = [];

    /** @var list<ProjectSubtype> */
    private array $importPendingSubtypes = [];

    /** @var array<string, true> */
    private array $importReservedCodes = [];

    /** @var array<string, string> internal field key => Chinese column title */
    public const COLUMN_HEADERS = [
        'org_code' => '组织编码',
        'project_name' => '项目名称',
        'project_type' => '项目类型',
        'project_subtype' => '项目子类型',
        'project_industry' => '项目行业',
        'project_location' => '项目地点',
        'project_nature' => '项目性质',
        'leader_name' => '负责人姓名',
        'leader_phone' => '负责人电话',
        'leader_email' => '负责人邮箱',
        'budget' => '项目预算',
        'funding_source' => '资金来源',
        'planned_start' => '计划开始日期',
        'planned_end' => '计划结束日期',
        'purpose' => '项目目的',
        'scale' => '项目规模',
        'registrant_name' => '登记人姓名',
        'registrant_phone' => '登记人电话',
        'registrant_org_code' => '登记人组织编码',
    ];

    private const FIELD_LEGEND = [
        'org_code' => '必填。与「组织机构」中的组织编码一致，例如 ORG-ZHCS-002。',
        'project_name' => '必填。',
        'project_type' => '必填。填写项目类型名称（与后台「项目类型」名称一致）；若不存在则自动新增。',
        'project_subtype' => '选填。填写项目子类型名称（须属于所选类型）；若不存在则自动新增；留空表示不关联子类型。',
        'project_industry' => '必填。',
        'project_location' => '必填。',
        'project_nature' => '必填。可用枚举值 government、enterprise，或中文：政府投资、企业投资。',
        'leader_name' => '必填。',
        'leader_phone' => '必填。11 位手机号。',
        'leader_email' => '选填。',
        'budget' => '必填。单位：元，可为数字。',
        'funding_source' => '必填。可用枚举值或中文标签（与政府财政拨款、银行贷款等一致）。',
        'planned_start' => '必填。YYYY-MM-DD 或 Excel 日期。',
        'planned_end' => '必填。须晚于计划开始日期。',
        'purpose' => '必填。',
        'scale' => '必填。',
        'registrant_name' => '必填。',
        'registrant_phone' => '必填。11 位手机号。',
        'registrant_org_code' => '选填。为空则默认与「组织编码」相同。',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly OrgAccessService $orgAccessService,
    ) {
    }

    public function createTemplateSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('项目导入');

        $col = 1;
        foreach (self::COLUMN_HEADERS as $header) {
            $sheet->setCellValue([$col, 1], $header);
            ++$col;
        }

        $sample = [
            'ORG-ZHCS-002',
            '批量导入示例项目（可删除本行后导入）',
            '集成类',
            '智慧城市',
            '信息技术',
            '上海市浦东新区',
            '政府投资',
            '张三',
            '13800138000',
            'zhangsan@example.com',
            1250000.5,
            '政府财政拨款',
            '2026-06-01',
            '2027-05-31',
            '用于验证批量导入流程的示例项目。',
            '建筑面积约 5000 平方米，工期约 12 个月。',
            '李四',
            '13900139000',
            '',
        ];

        foreach ($sample as $i => $value) {
            $sheet->setCellValue([$i + 1, 2], $value);
        }

        $legend = $spreadsheet->createSheet();
        $legend->setTitle('填写说明');
        $legend->setCellValue([1, 1], '列名');
        $legend->setCellValue([2, 1], '说明');
        $row = 2;
        foreach (self::COLUMN_HEADERS as $key => $zh) {
            $legend->setCellValue([1, $row], $zh);
            $legend->setCellValue([2, $row], self::FIELD_LEGEND[$key] ?? '');
            ++$row;
        }
        $legend->getColumnDimension('A')->setWidth(22);
        $legend->getColumnDimension('B')->setWidth(72);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * @param resource|string $stream URI such as php://output, or a writable stream resource
     */
    public function saveTemplateTo(mixed $stream): void
    {
        $spreadsheet = $this->createTemplateSpreadsheet();
        $writer = new Xlsx($spreadsheet);
        $writer->save($stream);
    }

    /**
     * @return array{imported: int, errors: list<string>}
     */
    public function importFromPath(string $path, User $user): array
    {
        $this->resetImportScratchState();

        try {
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getSheetByName('项目导入') ?? $spreadsheet->getActiveSheet();

            $headerToCol = $this->resolveHeaderColumns($sheet);
            $errors = [];
            $toPersist = [];

            $highestRow = $sheet->getHighestDataRow();
            for ($row = 2; $row <= $highestRow; ++$row) {
                if ($this->isRowEmpty($sheet, $row, $headerToCol)) {
                    continue;
                }

                $linePrefix = sprintf('第 %d 行：', $row);
                try {
                    $project = $this->buildProjectFromRow($sheet, $row, $headerToCol, $user);
                } catch (\InvalidArgumentException $e) {
                    $errors[] = $linePrefix.$e->getMessage();
                    continue;
                }

                $violations = $this->validator->validate($project);
                if (\count($violations) > 0) {
                    $msgs = [];
                    foreach ($violations as $v) {
                        $msgs[] = $v->getMessage();
                    }
                    $errors[] = $linePrefix.implode('；', $msgs);
                    continue;
                }

                $toPersist[] = $project;
            }

            if ($errors !== []) {
                return ['imported' => 0, 'errors' => $errors];
            }

            foreach ($this->importPendingTypes as $type) {
                foreach ($this->validator->validate($type) as $v) {
                    $errors[] = sprintf('自动创建的项目类型「%s」：%s', $type->getName() ?? '', $v->getMessage());
                }
            }

            foreach ($this->importPendingSubtypes as $subtype) {
                foreach ($this->validator->validate($subtype) as $v) {
                    $errors[] = sprintf(
                        '自动创建的项目子类型「%s」：%s',
                        $subtype->getName() ?? '',
                        $v->getMessage()
                    );
                }
            }

            if ($errors !== []) {
                return ['imported' => 0, 'errors' => $errors];
            }

            $this->entityManager->wrapInTransaction(function () use ($toPersist): void {
                foreach ($this->importPendingTypes as $type) {
                    $this->entityManager->persist($type);
                }
                foreach ($this->importPendingSubtypes as $subtype) {
                    $this->entityManager->persist($subtype);
                }
                foreach ($toPersist as $project) {
                    $project->setStatus(ProjectStatus::DRAFT);
                    $this->entityManager->persist($project);
                }
                $this->entityManager->flush();
            });

            return ['imported' => \count($toPersist), 'errors' => []];
        } finally {
            $this->resetImportScratchState();
        }
    }

    /**
     * @return array<string, int> field key => 1-based column index
     */
    private function resolveHeaderColumns(Worksheet $sheet): array
    {
        $highestColumn = $sheet->getHighestColumn(1);
        $maxCol = Coordinate::columnIndexFromString($highestColumn);

        $normalizedTitleToField = [];
        foreach (self::COLUMN_HEADERS as $fieldKey => $title) {
            $normalizedTitleToField[$this->normalizeHeader($title)] = $fieldKey;
        }

        $found = [];

        for ($col = 1; $col <= $maxCol; ++$col) {
            $raw = $sheet->getCell([$col, 1])->getValue();
            if ($raw === null || trim((string) $raw) === '') {
                continue;
            }

            $norm = $this->normalizeHeader((string) $raw);
            if (!isset($normalizedTitleToField[$norm])) {
                throw new \InvalidArgumentException(sprintf('无法识别的列标题：「%s」。请使用模板中的列名。', $raw));
            }

            $fieldKey = $normalizedTitleToField[$norm];
            if (isset($found[$fieldKey])) {
                throw new \InvalidArgumentException(sprintf('重复的列：「%s」。', self::COLUMN_HEADERS[$fieldKey]));
            }

            $found[$fieldKey] = $col;
        }

        $required = [
            'org_code',
            'project_name',
            'project_type',
            'project_industry',
            'project_location',
            'project_nature',
            'leader_name',
            'leader_phone',
            'budget',
            'funding_source',
            'planned_start',
            'planned_end',
            'purpose',
            'scale',
            'registrant_name',
            'registrant_phone',
        ];

        foreach ($required as $field) {
            if (!isset($found[$field])) {
                throw new \InvalidArgumentException(sprintf('缺少必填列「%s」。', self::COLUMN_HEADERS[$field]));
            }
        }

        return $found;
    }

    private function normalizeHeader(string $header): string
    {
        return mb_strtolower(preg_replace('/\s+/u', '', trim($header)) ?? '');
    }

    /**
     * @param array<string, int> $headerToCol
     */
    private function isRowEmpty(Worksheet $sheet, int $row, array $headerToCol): bool
    {
        foreach ($headerToCol as $col) {
            $cell = $sheet->getCell([$col, $row]);
            if (trim($this->stringCell($cell)) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, int> $headerToCol
     */
    private function buildProjectFromRow(Worksheet $sheet, int $row, array $headerToCol, User $user): Project
    {
        $orgCode = $this->requiredString($sheet, $row, $headerToCol, 'org_code');
        $org = $this->entityManager->getRepository(Org::class)->findOneBy(['orgCode' => $orgCode]);
        if (!$org instanceof Org) {
            throw new \InvalidArgumentException(sprintf('组织编码「%s」不存在。', $orgCode));
        }

        if (!$this->orgAccessService->canAccessOrg($user, $org)) {
            throw new \InvalidArgumentException(sprintf('无权导入组织编码「%s」下的项目。', $orgCode));
        }

        $typeName = $this->requiredString($sheet, $row, $headerToCol, 'project_type');
        if ($typeName === '') {
            throw new \InvalidArgumentException('项目类型不能为空。');
        }

        $projectType = $this->resolveProjectTypeByName($typeName);

        $subtypeName = $this->optionalString($sheet, $row, $headerToCol, 'project_subtype');
        $subtype = $subtypeName !== ''
            ? $this->resolveProjectSubtypeByName($subtypeName, $projectType)
            : null;

        $regOrgCode = $this->optionalString($sheet, $row, $headerToCol, 'registrant_org_code');
        $regOrg = $org;
        if ($regOrgCode !== '') {
            $resolved = $this->entityManager->getRepository(Org::class)->findOneBy(['orgCode' => $regOrgCode]);
            if (!$resolved instanceof Org) {
                throw new \InvalidArgumentException(sprintf('登记人组织编码「%s」不存在。', $regOrgCode));
            }
            $regOrg = $resolved;
        }

        if (!$this->orgAccessService->canAccessOrg($user, $regOrg)) {
            throw new \InvalidArgumentException(sprintf('无权使用登记人组织编码「%s」。', $regOrg->getOrgCode()));
        }

        $nature = $this->parseProjectNature($this->requiredString($sheet, $row, $headerToCol, 'project_nature'));
        $funding = $this->parseFundingSource($this->requiredString($sheet, $row, $headerToCol, 'funding_source'));

        $start = $this->requiredDate($sheet, $row, $headerToCol, 'planned_start');
        $end = $this->requiredDate($sheet, $row, $headerToCol, 'planned_end');

        $budget = $this->budgetFromSheet($sheet, $row, $headerToCol);

        $project = new Project();
        $project->setOrg($org);
        $project->setRegisteredBy($user);
        $project->setRegistrantOrganization($regOrg);
        $project->setProjectName($this->requiredString($sheet, $row, $headerToCol, 'project_name'));
        $project->setProjectType($projectType);
        $project->setProjectSubtype($subtype);
        $project->setProjectIndustry($this->requiredString($sheet, $row, $headerToCol, 'project_industry'));
        $project->setProjectLocation($this->requiredString($sheet, $row, $headerToCol, 'project_location'));
        $project->setProjectNature($nature);
        $project->setLeaderName($this->requiredString($sheet, $row, $headerToCol, 'leader_name'));
        $project->setLeaderPhone($this->requiredPhone($sheet, $row, $headerToCol, 'leader_phone'));
        $project->setLeaderEmail($this->optionalString($sheet, $row, $headerToCol, 'leader_email') ?: null);
        $project->setBudget($budget);
        $project->setFundingSource($funding);
        $project->setPlannedStartDate($start);
        $project->setPlannedEndDate($end);
        $project->setPurpose($this->requiredString($sheet, $row, $headerToCol, 'purpose'));
        $project->setScale($this->requiredString($sheet, $row, $headerToCol, 'scale'));
        $project->setRegistrantName($this->requiredString($sheet, $row, $headerToCol, 'registrant_name'));
        $project->setRegistrantPhone($this->requiredPhone($sheet, $row, $headerToCol, 'registrant_phone'));

        return $project;
    }

    private function parseProjectNature(string $raw): ProjectNature
    {
        $trim = trim($raw);
        foreach (ProjectNature::cases() as $case) {
            if ($case->value === $trim || $case->label() === $trim) {
                return $case;
            }
        }

        throw new \InvalidArgumentException(sprintf('无法解析项目性质「%s」。', $raw));
    }

    private function parseFundingSource(string $raw): FundingSource
    {
        $trim = trim($raw);
        foreach (FundingSource::cases() as $case) {
            if ($case->value === $trim || $case->label() === $trim) {
                return $case;
            }
        }

        throw new \InvalidArgumentException(sprintf('无法解析资金来源「%s」。', $raw));
    }

    private function normalizeBudget(string $raw): string
    {
        $trim = str_replace([',', ' ', "\xc2\xa0"], '', trim($raw));
        if ($trim === '') {
            throw new \InvalidArgumentException('项目预算不能为空。');
        }
        if (!is_numeric($trim)) {
            throw new \InvalidArgumentException(sprintf('项目预算「%s」不是有效数字。', $raw));
        }

        return number_format((float) $trim, 2, '.', '');
    }

    /**
     * @param array<string, int> $headerToCol
     */
    private function requiredPhone(Worksheet $sheet, int $row, array $headerToCol, string $field): string
    {
        $col = $headerToCol[$field] ?? null;
        if ($col === null) {
            return '';
        }

        return trim($this->phoneCellString($sheet->getCell([$col, $row])));
    }

    private function phoneCellString(Cell $cell): string
    {
        $value = $cell->getValue();
        if ($value === null || $value === '') {
            return '';
        }
        if (is_numeric($value)) {
            return sprintf('%.0f', (float) $value);
        }

        return preg_replace('/\D+/', '', $this->stringCell($cell)) ?? '';
    }

    /**
     * @param array<string, int> $headerToCol
     */
    private function budgetFromSheet(Worksheet $sheet, int $row, array $headerToCol): string
    {
        $col = $headerToCol['budget'];
        $cell = $sheet->getCell([$col, $row]);
        $value = $cell->getValue();

        if ($value === null || $value === '') {
            throw new \InvalidArgumentException('项目预算不能为空。');
        }

        if (is_numeric($value)) {
            return number_format((float) $value, 2, '.', '');
        }

        return $this->normalizeBudget($this->stringCell($cell));
    }

    /**
     * @param array<string, int> $headerToCol
     */
    private function requiredString(Worksheet $sheet, int $row, array $headerToCol, string $field): string
    {
        $col = $headerToCol[$field] ?? null;
        if ($col === null) {
            return '';
        }

        return trim($this->stringCell($sheet->getCell([$col, $row])));
    }

    /**
     * @param array<string, int> $headerToCol
     */
    private function optionalString(Worksheet $sheet, int $row, array $headerToCol, string $field): string
    {
        $col = $headerToCol[$field] ?? null;
        if ($col === null) {
            return '';
        }

        return trim($this->stringCell($sheet->getCell([$col, $row])));
    }

    /**
     * @param array<string, int> $headerToCol
     */
    private function requiredDate(Worksheet $sheet, int $row, array $headerToCol, string $field): \DateTimeImmutable
    {
        $col = $headerToCol[$field] ?? null;
        if ($col === null) {
            throw new \InvalidArgumentException(sprintf('缺少列「%s」。', self::COLUMN_HEADERS[$field]));
        }
        $cell = $sheet->getCell([$col, $row]);
        $dt = $this->parseDateCell($cell);
        if ($dt === null) {
            throw new \InvalidArgumentException(sprintf(
                '「%s」日期无效。',
                self::COLUMN_HEADERS[$field]
            ));
        }

        return $dt;
    }

    private function parseDateCell(Cell $cell): ?\DateTimeImmutable
    {
        $value = $cell->getValue();

        if ($value === null || $value === '') {
            return null;
        }

        if (ExcelDate::isDateTime($cell)) {
            $mutable = ExcelDate::excelToDateTimeObject((float) $cell->getCalculatedValue());

            return \DateTimeImmutable::createFromMutable($mutable)->setTime(0, 0);
        }

        if (\is_string($value)) {
            $trim = trim($value);
            foreach (['Y-m-d', 'Y/m/d', 'Y.m.d'] as $fmt) {
                $parsed = \DateTimeImmutable::createFromFormat($fmt, $trim);
                if ($parsed instanceof \DateTimeImmutable) {
                    return $parsed->setTime(0, 0);
                }
            }
        }

        return null;
    }

    private function resetImportScratchState(): void
    {
        $this->importTypesByNormalizedName = [];
        $this->importSubtypesByCacheKey = [];
        $this->importPendingTypes = [];
        $this->importPendingSubtypes = [];
        $this->importReservedCodes = [];
    }

    private function normalizeCatalogName(string $name): string
    {
        return trim($name);
    }

    private function catalogCacheKey(string $trimmedName): string
    {
        return mb_strtolower($trimmedName, 'UTF-8');
    }

    private function resolveProjectTypeByName(string $name): ProjectType
    {
        $trim = $this->normalizeCatalogName($name);
        if ($trim === '') {
            throw new \InvalidArgumentException('项目类型不能为空。');
        }

        $cacheKey = $this->catalogCacheKey($trim);
        if (isset($this->importTypesByNormalizedName[$cacheKey])) {
            return $this->importTypesByNormalizedName[$cacheKey];
        }

        $existing = $this->entityManager->getRepository(ProjectType::class)->findOneBy(['name' => $trim]);
        if ($existing instanceof ProjectType) {
            $this->importTypesByNormalizedName[$cacheKey] = $existing;

            return $existing;
        }

        $type = new ProjectType();
        $type->setName($trim);
        $type->setCode($this->allocateUniqueProjectTypeCode($trim));
        $type->setDescription('由项目批量导入自动创建');
        $type->setSortOrder(99);
        $type->setIsActive(true);

        $this->importPendingTypes[] = $type;
        $this->importTypesByNormalizedName[$cacheKey] = $type;

        return $type;
    }

    private function resolveProjectSubtypeByName(string $name, ProjectType $projectType): ProjectSubtype
    {
        $trim = $this->normalizeCatalogName($name);
        if ($trim === '') {
            throw new \InvalidArgumentException('项目子类型不能为空。');
        }

        $cacheKey = spl_object_id($projectType).'|'.$this->catalogCacheKey($trim);
        if (isset($this->importSubtypesByCacheKey[$cacheKey])) {
            return $this->importSubtypesByCacheKey[$cacheKey];
        }

        if ($projectType->getId() !== null) {
            $existing = $this->entityManager->getRepository(ProjectSubtype::class)->findOneBy([
                'name' => $trim,
                'projectType' => $projectType,
            ]);
            if ($existing instanceof ProjectSubtype) {
                $this->importSubtypesByCacheKey[$cacheKey] = $existing;

                return $existing;
            }
        }

        $subtype = new ProjectSubtype();
        $subtype->setProjectType($projectType);
        $subtype->setName($trim);
        $subtype->setCode($this->allocateUniqueProjectSubtypeCode($trim));
        $subtype->setSortOrder(99);
        $subtype->setIsActive(true);

        $this->importPendingSubtypes[] = $subtype;
        $this->importSubtypesByCacheKey[$cacheKey] = $subtype;

        return $subtype;
    }

    private function slugCodeBase(string $name, string $emptyFallback): string
    {
        $trim = trim($name);
        $permalink = Pinyin::permalink($trim, '_');
        $slug = strtolower((string) preg_replace('/[^a-z_]/', '_', $permalink));
        $slug = (string) preg_replace('/_+/', '_', $slug);
        $slug = trim($slug, '_');
        if ($slug === '') {
            $slug = $emptyFallback;
        }
        if (\strlen($slug) > 35) {
            $slug = substr($slug, 0, 35);
            $slug = rtrim($slug, '_');
        }

        return $slug;
    }

    private function allocateUniqueProjectTypeCode(string $displayName): string
    {
        $base = $this->slugCodeBase($displayName, 'import_type');

        return $this->reserveUniqueCode($base, fn (string $c): bool => $this->isProjectTypeCodeUnavailable($c));
    }

    private function allocateUniqueProjectSubtypeCode(string $displayName): string
    {
        $base = $this->slugCodeBase($displayName, 'import_subtype');

        return $this->reserveUniqueCode($base, fn (string $c): bool => $this->isProjectSubtypeCodeUnavailable($c));
    }

    private function isProjectTypeCodeUnavailable(string $code): bool
    {
        if (isset($this->importReservedCodes[$code])) {
            return true;
        }

        return null !== $this->entityManager->getRepository(ProjectType::class)->findOneBy(['code' => $code]);
    }

    private function isProjectSubtypeCodeUnavailable(string $code): bool
    {
        if (isset($this->importReservedCodes[$code])) {
            return true;
        }

        return null !== $this->entityManager->getRepository(ProjectSubtype::class)->findOneBy(['code' => $code]);
    }

    private function reserveUniqueCode(string $base, callable $isTaken): string
    {
        $candidate = $base;
        $n = 0;
        while ($isTaken($candidate)) {
            ++$n;
            $suffix = '_'.$n;
            $maxBaseLen = 50 - \strlen($suffix);
            $truncBase = \strlen($base) > $maxBaseLen ? substr($base, 0, max(1, $maxBaseLen)) : $base;
            $truncBase = rtrim($truncBase, '_');
            $candidate = $truncBase.$suffix;
        }

        $this->importReservedCodes[$candidate] = true;

        return $candidate;
    }

    private function stringCell(Cell $cell): string
    {
        return trim((string) $cell->getFormattedValue());
    }
}
