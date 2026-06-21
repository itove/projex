<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Org;
use App\Entity\Project;
use App\Entity\ProjectType;
use App\Entity\User;
use App\Enum\FundingSource;
use App\Enum\ProjectNature;
use App\Enum\ProjectStatus;
use App\Service\OrgAccessService;
use App\Service\ProjectDisplayService;
use App\Service\ProjectLockingService;
use App\Service\ProjectNavigationService;
use App\Service\ProjectNumberGenerator;
use App\Service\ProjectSpreadsheetImportService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use App\Form\ImageType;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Security\Voter\ProjectVoter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ProjectNumberGenerator $numberGenerator,
        private readonly ProjectLockingService $lockingService,
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly ProjectDisplayService $displayService,
        private readonly ProjectSpreadsheetImportService $spreadsheetImportService,
        private readonly OrgAccessService $orgAccessService,
        private readonly ProjectNavigationService $projectNavigationService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('项目基础信息')
            ->setEntityLabelInPlural('项目基础信息')
            ->setPageTitle(Crud::PAGE_INDEX, '项目基础信息列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建项目')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑项目')
            ->setPageTitle(Crud::PAGE_DETAIL, '项目详情')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['projectName', 'projectNumber', 'leaderName', 'projectLocation'])
            ->overrideTemplate('crud/detail', 'admin/project/detail.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $context = $this->getContext();
        $project = $context?->getEntity()?->getInstance();
        $lockCoreFields = $project instanceof Project && $this->lockingService->shouldLockCoreFields($project);

        // Project Basic Info Section
        yield TextField::new('projectName', '项目名称')
            ->setRequired(true)
            ->setColumns(6)
            ->setFormTypeOption('disabled', $lockCoreFields);

        yield TextField::new('projectNumber', '项目编号')
            ->hideOnForm()
            ->setColumns(6);

        yield AssociationField::new('org', '所属组织')
            ->setRequired(true)
            ->setColumns(6)
            ->autocomplete()
            ->setQueryBuilder(function ($queryBuilder) {
                $user = $this->getUser();
                if (!$user instanceof User) {
                    return $queryBuilder;
                }

                $accessibleOrgIds = $this->orgAccessService->getAccessibleOrgIds($user);
                if ($accessibleOrgIds === null) {
                    return $queryBuilder;
                }

                if ($accessibleOrgIds === []) {
                    return $queryBuilder->andWhere('1 = 0');
                }

                return $queryBuilder
                    ->andWhere('entity.id IN (:accessibleOrgIds)')
                    ->setParameter('accessibleOrgIds', $accessibleOrgIds);
            })
            ->setHelp('项目所属的组织机构');

        yield AssociationField::new('projectType', '项目类型')
            ->setRequired(true)
            ->setColumns(6)
            ->autocomplete()
            ->setQueryBuilder(function ($queryBuilder) {
                return $queryBuilder
                    ->andWhere('entity.isActive = :active')
                    ->setParameter('active', true)
                    ->orderBy('entity.sortOrder', 'ASC')
                    ->addOrderBy('entity.name', 'ASC');
            });

        yield AssociationField::new('projectSubtype', '项目子类型')
            ->setRequired(false)
            ->setColumns(6)
            ->autocomplete()
            ->setQueryBuilder(function ($queryBuilder) {
                return $queryBuilder
                    ->andWhere('entity.isActive = :active')
                    ->setParameter('active', true)
                    ->orderBy('entity.sortOrder', 'ASC')
                    ->addOrderBy('entity.name', 'ASC');
            })
            ->setHelp('先选择项目类型，然后选择对应的子类型')
        ;

        yield TextField::new('projectIndustry', '项目行业')
            ->setRequired(true)
            ->setColumns(6)
            ->hideOnIndex();

        yield TextField::new('projectLocation', '项目地点')
            ->setRequired(true)
            ->setColumns(6);

        yield ChoiceField::new('projectNature', '项目性质')
            ->setChoices(array_combine(
                array_map(fn(ProjectNature $nature) => $nature->label(), ProjectNature::cases()),
                ProjectNature::cases()
            ))
            ->setRequired(true)
            ->setColumns(6)
            ->setFormTypeOption('disabled', $lockCoreFields)
            ->formatValue(fn($value) => $value?->label())
        ;

        // Project Leader Section
        yield TextField::new('leaderName', '负责人姓名')
            ->setRequired(true)
            ->setColumns(4)
            ->hideOnDetail()
        ;

        yield TextField::new('leaderPhone', '负责人电话')
            ->setRequired(true)
            ->setColumns(4)
            ->hideOnDetail()
            ->hideOnIndex();

        // Project Parameters Section
        yield MoneyField::new('budget', '项目预算')
            ->setCurrency('CNY')
            ->setRequired(true)
            ->setColumns(6)
            ->setFormTypeOption('disabled', $lockCoreFields)
            ->setHelp('单位：元')
            ->hideOnIndex();

        yield ChoiceField::new('fundingSource', '资金来源')
            ->setChoices(array_combine(
                array_map(fn(FundingSource $source) => $source->label(), FundingSource::cases()),
                FundingSource::cases()
            ))
            ->setRequired(true)
            ->setColumns(6)
            ->setFormTypeOption('disabled', $lockCoreFields)
            ->formatValue(fn($value) => $value?->label())
            ->hideOnIndex();

        yield DateField::new('plannedStartDate', '计划开始日期')
            ->setRequired(true)
            ->setColumns(6)
            ->setFormTypeOption('disabled', $lockCoreFields)
            ->hideOnIndex();

        yield DateField::new('plannedEndDate', '计划结束日期')
            ->setRequired(true)
            ->setColumns(6)
            ->setFormTypeOption('disabled', $lockCoreFields)
            ->hideOnIndex();

        yield TextField::new('leader', '负责人')
            ->onlyOnDetail();

        yield TextField::new('registrant', '登记人')
            ->onlyOnDetail();

        // Registrant Info Section
        yield AssociationField::new('registeredBy', '登记人')
            ->autocomplete()
            ->setColumns(4)
            ->hideOnIndex()
            ->hideOnDetail()
            ->setHelp('系统会自动记录登记人信息');

        yield TextField::new('registrantName', '登记人姓名')
            ->setRequired(true)
            ->setColumns(4)
            ->hideOnDetail()
            ->hideOnIndex();

        // yield AssociationField::new('registrantOrganization', '登记人单位')
        //     ->autocomplete()
        //     ->setColumns(4)
        //     ->hideOnDetail()
        //     ->hideOnIndex();

        yield TextField::new('registrantPhone', '登记人电话')
            ->setRequired(true)
            ->setColumns(4)
            ->hideOnDetail()
            ->hideOnIndex();

        // Optional Fields Section
        yield TextareaField::new('remarks', '备注')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex();

        yield TextareaField::new('specialNotes', '特殊说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex();

        yield TextareaField::new('prerequisiteNotes', '前置条件说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex();

        // System Fields (display only)
        yield ChoiceField::new('status', '状态')
            ->setChoices(array_combine(
                array_map(fn(ProjectStatus $status) => $status->label(), ProjectStatus::cases()),
                ProjectStatus::cases()
            ))
            ->hideOnForm()
            ->setColumns(3)
            ->formatValue(fn($value) => $value?->label());

        yield DateField::new('createdAt', '创建时间')
            ->hideOnForm()
            ->setColumns(3);

        yield DateField::new('updatedAt', '更新时间')
            ->hideOnForm()
            ->hideOnIndex()
            ->setColumns(3);

        yield TextareaField::new('purpose', '项目目的')
            ->setRequired(true)
            ->setColumns(12)
            ->hideOnIndex();

        yield TextareaField::new('scale', '项目规模')
            ->setRequired(true)
            ->setColumns(12)
            ->hideOnIndex();

        // Title Image & Slides Section
        yield TextField::new('titleImageFile', '封面图片')
            ->setFormType(\Vich\UploaderBundle\Form\Type\VichImageType::class)
            ->onlyOnForms()
            ->setRequired(false)
            ->setColumns(12)
            ->setHelp('项目封面图片，支持格式: JPEG, PNG, GIF, WebP，最大 10MB');

        yield \EasyCorp\Bundle\EasyAdminBundle\Field\ImageField::new('titleImageName', '封面图片')
            ->setBasePath('/uploads/title-images')
            ->hideOnForm()
            ->hideOnIndex()
            ->setColumns(12);

        $imagesField = CollectionField::new('images', '项目图集')
            ->setEntryType(ImageType::class)
            ->setRequired(false)
            ->allowAdd()
            ->allowDelete()
            ->hideOnIndex()
            ->renderExpanded()
            ->setColumns(12)
            ->setHelp('项目幻灯片图集，每张图片支持格式: JPEG, PNG, GIF, WebP，最大 10MB');

        if ($pageName === Crud::PAGE_DETAIL) {
            $imagesField->setTemplatePath('admin/field/project_images_lightbox.html.twig');
        }

        yield $imagesField;
    }

    public function configureActions(Actions $actions): Actions
    {
        $submitAction = Action::new('submitForReview', '提交审核', 'fa fa-check')
            ->linkToCrudAction('submitForReview')
            ->displayIf(fn(Project $project) => $project->getStatus() === ProjectStatus::DRAFT);

        $canImport = fn (?object $_entity = null): bool => $this->isGranted('ROLE_SYSTEM_ADMIN')
            || $this->isGranted('ROLE_PROJECT_MANAGER');

        $batchImport = Action::new('batchImport', '批量导入', 'fa fa-file-import')
            ->linkToCrudAction('batchImport')
            ->createAsGlobalAction()
            ->displayIf($canImport);

        $downloadImportTemplate = Action::new('downloadImportTemplate', '下载导入模板', 'fa fa-download')
            ->linkToCrudAction('downloadImportTemplate')
            ->createAsGlobalAction()
            ->displayIf($canImport);

        $actions = $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $batchImport)
            ->add(Crud::PAGE_INDEX, $downloadImportTemplate)
            ->add(Crud::PAGE_EDIT, $submitAction);

        // Permission controls based on roles (Section 4.3.4)

        // Only admins can delete projects
        $actions->setPermission(Action::DELETE, 'ROLE_SYSTEM_ADMIN');

        // Project managers can edit (already filtered to their own projects)
        // Supervisors and admins can view but not edit unless they're also project managers
        if ($this->isGranted('ROLE_SUPERVISOR') && !$this->isGranted('ROLE_PROJECT_MANAGER')) {
            $actions->disable(Action::NEW, Action::EDIT);
        }

        // Auditors can only view (filtered by assignment, currently showing none)
        if ($this->isGranted('ROLE_AUDITOR') &&
            !$this->isGranted('ROLE_PROJECT_MANAGER') &&
            !$this->isGranted('ROLE_SUPERVISOR')) {
            $actions->disable(Action::NEW, Action::EDIT);
        }

        return $actions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('status', '状态')
                ->setChoices(array_combine(
                    array_map(fn(ProjectStatus $status) => $status->label(), ProjectStatus::cases()),
                    array_map(fn(ProjectStatus $status) => $status->value, ProjectStatus::cases())
                ))
            )
            ->add(EntityFilter::new('projectType', '项目类型'))
            ->add(EntityFilter::new('projectSubtype', '项目子类型'))
            ->add(EntityFilter::new('org', '所属组织'))
            ->add(DateTimeFilter::new('createdAt', '创建时间'));
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Project) {
            $this->assertProjectOrgAccessible($entityInstance);
            $entityInstance->setStatus(ProjectStatus::DRAFT);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Project) {
            $this->assertProjectOrgAccessible($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function detail(AdminContext $context): KeyValueStore|Response
    {
        $project = $context->getEntity()->getInstance();
        if ($project instanceof Project) {
            $this->denyAccessUnlessGranted(ProjectVoter::VIEW, $project);
        }

        return parent::detail($context);
    }

    public function edit(AdminContext $context): KeyValueStore|Response
    {
        $project = $context->getEntity()->getInstance();
        if ($project instanceof Project) {
            $this->denyAccessUnlessGranted(ProjectVoter::MANAGE, $project);
        }

        return parent::edit($context);
    }

    public function delete(AdminContext $context): KeyValueStore|Response
    {
        $project = $context->getEntity()->getInstance();
        if ($project instanceof Project) {
            $this->denyAccessUnlessGranted(ProjectVoter::VIEW, $project);
        }

        return parent::delete($context);
    }

    private function assertProjectOrgAccessible(Project $project): void
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('未登录用户无法操作项目。');
        }

        if (!$this->orgAccessService->canAccessOrg($user, $project->getOrg())) {
            throw $this->createAccessDeniedException('无权将项目分配到该组织。');
        }
    }

    public function submitForReview(AdminContext $context): RedirectResponse
    {
        $project = $context->getEntity()->getInstance();

        if (!$project instanceof Project) {
            $this->addFlash('error', '无效的项目实例');
            return $this->redirect($this->adminUrlGenerator->setAction(Action::INDEX)->generateUrl());
        }

        $this->denyAccessUnlessGranted(ProjectVoter::MANAGE, $project);

        if ($project->getStatus() !== ProjectStatus::DRAFT) {
            $this->addFlash('error', '只有草稿状态的项目可以提交审核');
            return $this->redirect($this->adminUrlGenerator->setAction(Action::INDEX)->generateUrl());
        }

        // Generate project number if not exists
        if ($project->getProjectNumber() === null) {
            $projectNumber = $this->numberGenerator->generate();
            $project->setProjectNumber($projectNumber);
        }

        // Change status to REGISTERED
        $project->setStatus(ProjectStatus::REGISTERED);

        // Lock core fields
        $this->lockingService->lockCoreFields($project);

        // Persist changes
        $this->entityManager->flush();

        $this->addFlash('success', sprintf(
            '项目 "%s" (编号: %s) 提交成功',
            $project->getProjectName(),
            $project->getProjectNumber()
        ));

        return $this->redirect($this->adminUrlGenerator->setAction(Action::INDEX)->generateUrl());
    }

    public function batchImport(AdminContext $context): Response
    {
        $this->denyAccessUnlessProjectImporter();

        $request = $context->getRequest();
        $indexUrl = $this->adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl();
        $importPageUrl = $this->adminUrlGenerator->setController(self::class)->setAction('batchImport')->generateUrl();

        if ($request->isMethod('POST')) {
            $token = (string) $request->request->get('_csrf_token');
            if (!$this->isCsrfTokenValid('ea_project_import', $token)) {
                $this->addFlash('error', '无效的请求令牌，请刷新页面后重试。');

                return $this->redirect($importPageUrl);
            }

            /** @var UploadedFile|null $upload */
            $upload = $request->files->get('import_file');
            if (!$upload instanceof UploadedFile || !$upload->isValid()) {
                $this->addFlash('error', '请选择有效的 Excel 文件。');

                return $this->redirect($importPageUrl);
            }

            $name = strtolower($upload->getClientOriginalExtension());
            if ($name !== 'xlsx') {
                $this->addFlash('error', '仅支持 .xlsx 格式（Office Open XML）。');

                return $this->redirect($importPageUrl);
            }

            $user = $this->getUser();
            if (!$user instanceof User) {
                $this->addFlash('error', '当前用户无效，无法导入。');

                return $this->redirect($indexUrl);
            }

            try {
                $result = $this->spreadsheetImportService->importFromPath($upload->getRealPath(), $user);
            } catch (\Throwable $e) {
                $this->addFlash('error', '读取表格失败：'.$e->getMessage());

                return $this->redirect($importPageUrl);
            }

            if ($result['errors'] !== []) {
                foreach ($result['errors'] as $message) {
                    $this->addFlash('error', $message);
                }

                return $this->redirect($importPageUrl);
            }

            if ($result['imported'] === 0) {
                $this->addFlash('warning', '没有找到可导入的数据行（空行已跳过）。');
            } else {
                $this->addFlash('success', sprintf('成功导入 %d 条项目。', $result['imported']));
            }

            return $this->redirect($indexUrl);
        }

        return $this->render('admin/project/import.html.twig', [
            'import_submit_url' => $importPageUrl,
            'template_download_url' => $this->adminUrlGenerator->setController(self::class)->setAction('downloadImportTemplate')->generateUrl(),
            'back_url' => $indexUrl,
            'csrf_token_id' => 'ea_project_import',
        ]);
    }

    public function downloadImportTemplate(AdminContext $context): StreamedResponse
    {
        $this->denyAccessUnlessProjectImporter();

        $response = new StreamedResponse(function (): void {
            $this->spreadsheetImportService->saveTemplateTo('php://output');
        });
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="project_import_template.xlsx"');

        return $response;
    }

    private function denyAccessUnlessProjectImporter(): void
    {
        if (!$this->isGranted('ROLE_SYSTEM_ADMIN') && !$this->isGranted('ROLE_PROJECT_MANAGER')) {
            throw $this->createAccessDeniedException('您没有批量导入项目的权限。');
        }
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        if (Crud::PAGE_DETAIL === $responseParameters->get('pageName')) {
            $entity = $responseParameters->get('entity');
            $project = $entity->getInstance();

            $stages = $this->displayService->getLifecycleStages($project);
            $summary = $this->displayService->getProjectSummary($project);
            $summary['links'] = $this->projectNavigationService->buildProjectDetailSummaryLinks(
                $stages,
                (int) $project->getId(),
            );

            $responseParameters->set('project', $project);
            $responseParameters->set('stages', $this->projectNavigationService->enrichStagesWithUrls(
                $stages,
                (int) $project->getId(),
            ));
            $responseParameters->set('summary', $summary);
        }

        return $responseParameters;
    }

    /**
     * Filter projects by org hierarchy scope.
     * Users can only see projects belonging to their org or subsidiaries.
     * Role controls view vs manage actions elsewhere.
     */
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $qb;
        }

        $this->orgAccessService->applyProjectOrgScope($qb, $user, 'entity');

        $orgId = $this->getContext()?->getRequest()?->query->getInt('orgId');
        if ($orgId > 0) {
            $org = $this->entityManager->getRepository(Org::class)->find($orgId);
            if ($org instanceof Org && $this->orgAccessService->canAccessOrg($user, $org)) {
                $qb->andWhere('entity.org = :filterOrgId')
                    ->setParameter('filterOrgId', $orgId);
            }
        }

        $request = $this->getContext()?->getRequest();
        if ($request !== null) {
            $this->projectNavigationService->applyListFilters($qb, $request, 'entity');
        }

        return $qb;
    }

    /**
     * Auto-populate registeredBy and org when creating a new project
     */
    public function createEntity(string $entityFqcn): Project
    {
        $project = new Project();

        /** @var User|null $user */
        $user = $this->getUser();

        if ($user) {
            $project->setRegisteredBy($user);
            $project->setOrg($user->getOrg());
            $project->setRegistrantOrganization($user->getOrg());
            $project->setRegistrantName($user->getName());
            $project->setRegistrantPhone($user->getPhone() ?? '');
        }

        return $project;
    }
}
