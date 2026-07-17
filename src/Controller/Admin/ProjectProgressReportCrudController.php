<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Entity\ProjectProgressReport;
use App\Entity\User;
use App\Enum\ProjectProgressReportStatus;
use App\Exception\MissingProgressReportIntervalException;
use App\Repository\ProjectRepository;
use App\Security\Voter\ProjectVoter;
use App\Service\OrgAccessService;
use App\Service\ProjectProgressReportService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class ProjectProgressReportCrudController extends AbstractOrgScopedLifecycleCrudController
{
    public function __construct(
        OrgAccessService $orgAccessService,
        private readonly ProjectRepository $projectRepository,
        private readonly ProjectProgressReportService $progressReportService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
        parent::__construct($orgAccessService);
    }

    public static function getEntityFqcn(): string
    {
        return ProjectProgressReport::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('进度报告')
            ->setEntityLabelInPlural('进度报告')
            ->setPageTitle(Crud::PAGE_INDEX, '进度报告列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建进度报告')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑进度报告')
            ->setPageTitle(Crud::PAGE_DETAIL, '进度报告详情')
            ->setDefaultSort(['periodStartDate' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['currentProgressSummary', 'nextPeriodPlan', 'issues']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('project', '所属项目')
            ->setRequired(true)
            ->autocomplete()
            ->setColumns(6)
            ->setFormTypeOption('disabled', $pageName === Crud::PAGE_EDIT)
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
                    ->andWhere('entity.org IN (:accessibleOrgIds)')
                    ->setParameter('accessibleOrgIds', $accessibleOrgIds);
            });

        // Period is system-computed from the project's reporting cadence - never user input.
        yield DateField::new('periodStartDate', '本期开始日期')
            ->hideOnForm()
            ->setColumns(3);

        yield DateField::new('periodEndDate', '本期截止日期')
            ->hideOnForm()
            ->setColumns(3);

        yield IntegerField::new('progressPercentage', '完成百分比')
            ->setRequired(true)
            ->setColumns(3)
            ->formatValue(static fn (?int $value) => $value !== null ? $value.'%' : '—');

        yield ChoiceField::new('statusTag', '进度状态')
            ->setChoices($this->statusChoices())
            ->renderAsBadges([
                ProjectProgressReportStatus::NORMAL->value => 'success',
                ProjectProgressReportStatus::AT_RISK->value => 'warning',
                ProjectProgressReportStatus::DELAYED->value => 'danger',
            ])
            ->setColumns(3);

        yield TextareaField::new('currentProgressSummary', '本月进度')
            ->setRequired(true)
            ->setColumns(12)
            ->hideOnIndex();

        yield TextareaField::new('nextPeriodPlan', '下月计划')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex();

        yield TextareaField::new('issues', '存在的困难和问题')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex();

        if ($pageName === Crud::PAGE_INDEX) {
            yield TextareaField::new('currentProgressSummary', '本月进度')
                ->formatValue(static fn (?string $value) => $value !== null && $value !== ''
                    ? (mb_strlen($value) > 40 ? mb_substr($value, 0, 40).'…' : $value)
                    : '—');
        }

        yield AssociationField::new('reportedBy', '填报人')
            ->hideOnForm()
            ->setColumns(4);

        yield DateTimeField::new('createdAt', '填报时间')
            ->hideOnForm()
            ->setColumns(4);

        yield DateTimeField::new('updatedAt', '更新时间')
            ->hideOnForm()
            ->hideOnIndex()
            ->setColumns(4);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission(Action::NEW, 'ROLE_USER')
            ->setPermission(Action::EDIT, 'ROLE_USER')
            ->setPermission(Action::DELETE, 'ROLE_USER');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('project', '项目'))
            ->add(ChoiceFilter::new('statusTag', '进度状态')->setChoices($this->statusChoices()))
            ->add(DateTimeFilter::new('periodStartDate', '本期开始日期'));
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        return $qb
            ->addOrderBy('entity.periodStartDate', 'DESC')
            ->addOrderBy('entity.createdAt', 'DESC');
    }

    /**
     * Auto-populate project (from ?project= query param) and pre-compute the
     * current reporting period so the fields are never manually entered.
     */
    public function createEntity(string $entityFqcn): ProjectProgressReport
    {
        $report = new ProjectProgressReport();
        $request = $this->getContext()?->getRequest();
        $projectId = $request?->query->getInt('project') ?? 0;

        if ($projectId > 0) {
            $project = $this->projectRepository->find($projectId);
            if ($project instanceof Project) {
                $report->setProject($project);
                $this->applyCurrentPeriod($report, $project);
            }
        }

        return $report;
    }

    public function new(AdminContext $context): KeyValueStore|Response
    {
        $projectId = $context->getRequest()->query->getInt('project');
        if ($projectId > 0) {
            $project = $this->projectRepository->find($projectId);
            if ($project instanceof Project) {
                $this->denyAccessUnlessGranted(ProjectVoter::MANAGE, $project);

                $period = $this->progressReportService->getCurrentPeriodRange($project);
                if ($period === null) {
                    $this->addFlash('error', '该项目未设置进度填报周期，请先在项目信息中配置「进度填报周期」（每周/每月）。');

                    return $this->redirect(
                        $this->adminUrlGenerator
                            ->unsetAll()
                            ->setController(ProjectCrudController::class)
                            ->setAction(Action::EDIT)
                            ->setEntityId($project->getId())
                            ->generateUrl()
                    );
                }

                $existing = $this->progressReportService->getCurrentPeriodReport($project);
                if ($existing !== null) {
                    $this->addFlash('info', '本期进度报告已存在，请在此编辑。');

                    return $this->redirect(
                        $this->adminUrlGenerator
                            ->setController(self::class)
                            ->setAction(Action::EDIT)
                            ->setEntityId($existing->getId())
                            ->generateUrl()
                    );
                }
            }
        }

        // Belt-and-suspenders: the checks above (and the UniqueEntity
        // constraint on the entity) cover the normal flow, but a project
        // picked from the generic "new" form's dropdown never gets its
        // period pre-computed before validation runs, and concurrent
        // submissions can still race past both checks. Catch the resulting
        // DB-level unique violation / missing-interval case here instead of
        // surfacing a raw error page.
        try {
            return parent::new($context);
        } catch (MissingProgressReportIntervalException $exception) {
            $this->addFlash('error', $exception->getMessage());

            $project = $exception->getProject();
            if ($project instanceof Project && $project->getId() !== null) {
                return $this->redirect(
                    $this->adminUrlGenerator
                        ->unsetAll()
                        ->setController(ProjectCrudController::class)
                        ->setAction(Action::EDIT)
                        ->setEntityId($project->getId())
                        ->generateUrl()
                );
            }

            return $this->redirect(
                $this->adminUrlGenerator->unsetAll()->setController(self::class)->setAction(Action::INDEX)->generateUrl()
            );
        } catch (UniqueConstraintViolationException) {
            $this->addFlash('error', '本期进度报告已存在，请勿重复填报。');

            return $this->redirect(
                $this->adminUrlGenerator->unsetAll()->setController(self::class)->setAction(Action::INDEX)->generateUrl()
            );
        }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof ProjectProgressReport) {
            $this->assertReportAccess($entityInstance, ProjectVoter::MANAGE);

            $project = $entityInstance->getProject();
            if ($project instanceof Project) {
                $this->applyCurrentPeriod($entityInstance, $project);
            }

            if ($entityInstance->getPeriodStartDate() === null) {
                throw new MissingProgressReportIntervalException($project);
            }

            $user = $this->getUser();
            if ($user instanceof User) {
                $entityInstance->setReportedBy($user);
            }
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof ProjectProgressReport) {
            $this->assertReportAccess($entityInstance, ProjectVoter::MANAGE);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof ProjectProgressReport) {
            $this->assertReportAccess($entityInstance, ProjectVoter::MANAGE);
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }

    public function detail(AdminContext $context): KeyValueStore|Response
    {
        $report = $context->getEntity()->getInstance();
        if ($report instanceof ProjectProgressReport) {
            $this->assertReportAccess($report, ProjectVoter::VIEW);
        }

        return parent::detail($context);
    }

    public function edit(AdminContext $context): KeyValueStore|Response
    {
        $report = $context->getEntity()->getInstance();
        if ($report instanceof ProjectProgressReport) {
            $this->assertReportAccess($report, ProjectVoter::MANAGE);
        }

        return parent::edit($context);
    }

    public function delete(AdminContext $context): KeyValueStore|Response
    {
        $report = $context->getEntity()->getInstance();
        if ($report instanceof ProjectProgressReport) {
            $this->assertReportAccess($report, ProjectVoter::MANAGE);
        }

        return parent::delete($context);
    }

    private function applyCurrentPeriod(ProjectProgressReport $report, Project $project): void
    {
        $period = $this->progressReportService->getCurrentPeriodRange($project);
        if ($period === null) {
            return;
        }

        $report->setPeriodStartDate($period['start']);
        $report->setPeriodEndDate($period['due']);
    }

    /**
     * @return array<string, ProjectProgressReportStatus>
     */
    private function statusChoices(): array
    {
        $choices = [];
        foreach (ProjectProgressReportStatus::cases() as $status) {
            $choices[$status->label()] = $status;
        }

        return $choices;
    }

    private function assertReportAccess(ProjectProgressReport $report, string $attribute): void
    {
        $project = $report->getProject();
        if (!$project instanceof Project) {
            throw $this->createAccessDeniedException('进度报告必须关联到项目。');
        }

        $this->denyAccessUnlessGranted($attribute, $project);
    }
}
