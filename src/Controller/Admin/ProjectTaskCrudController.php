<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Entity\ProjectTask;
use App\Entity\User;
use App\Enum\ProjectLifecycleStage;
use App\Enum\ProjectTaskPriority;
use App\Enum\ProjectTaskStatus;
use App\Repository\ProjectRepository;
use App\Security\Voter\ProjectVoter;
use App\Service\OrgAccessService;
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
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\HttpFoundation\Response;

class ProjectTaskCrudController extends AbstractOrgScopedLifecycleCrudController
{
    public function __construct(
        OrgAccessService $orgAccessService,
        private readonly ProjectRepository $projectRepository,
    ) {
        parent::__construct($orgAccessService);
    }

    public static function getEntityFqcn(): string
    {
        return ProjectTask::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('项目任务')
            ->setEntityLabelInPlural('项目任务')
            ->setPageTitle(Crud::PAGE_INDEX, '项目任务列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建项目任务')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑项目任务')
            ->setPageTitle(Crud::PAGE_DETAIL, '项目任务详情')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['title', 'responsibleUnit', 'cooperatingUnit', 'progressText']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('project', '所属项目')
            ->setRequired(true)
            ->autocomplete()
            ->setColumns(6)
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

        yield ChoiceField::new('lifecycleStage', '所属阶段')
            ->setChoices($this->lifecycleStageChoices())
            ->setRequired(false)
            ->setColumns(6)
            ->formatValue(static fn (?ProjectLifecycleStage $stage) => $stage?->label() ?? '—');

        yield TextField::new('title', '任务名称')
            ->setRequired(true)
            ->setColumns(6);

        yield ChoiceField::new('status', '状态')
            ->setChoices($this->statusChoices())
            ->renderAsBadges([
                ProjectTaskStatus::PENDING->value => 'secondary',
                ProjectTaskStatus::IN_PROGRESS->value => 'warning',
                ProjectTaskStatus::DONE->value => 'success',
                ProjectTaskStatus::CANCELLED->value => 'danger',
            ])
            ->setColumns(4);

        yield ChoiceField::new('priority', '优先级')
            ->setChoices($this->priorityChoices())
            ->setColumns(4);

        yield AssociationField::new('assignee', '负责人')
            ->autocomplete()
            ->setRequired(false)
            ->setColumns(4)
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

        yield TextField::new('responsibleUnit', '责任单位')
            ->setRequired(false)
            ->setColumns(6);

        yield TextField::new('cooperatingUnit', '配合单位')
            ->setRequired(false)
            ->setColumns(6);

        yield DateField::new('startDate', '开始时间')
            ->setRequired(false)
            ->setColumns(4);

        yield DateField::new('endDate', '结束时间')
            ->setRequired(false)
            ->setColumns(4);

        yield IntegerField::new('durationDays', '天数')
            ->hideOnForm()
            ->setColumns(4);

        yield DateField::new('dueDate', '截止日期')
            ->setRequired(false)
            ->setColumns(4);

        yield TextareaField::new('progressText', '当前进度')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex();

        yield TextareaField::new('description', '备注')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex();

        if ($pageName === Crud::PAGE_INDEX) {
            yield TextField::new('progressText', '当前进度')
                ->formatValue(static fn (?string $value) => $value !== null && $value !== ''
                    ? (mb_strlen($value) > 40 ? mb_substr($value, 0, 40).'…' : $value)
                    : '—');
        }

        yield AssociationField::new('createdBy', '创建人')
            ->hideOnForm()
            ->setColumns(4);

        yield DateTimeField::new('completedAt', '完成时间')
            ->hideOnForm()
            ->hideOnIndex()
            ->setColumns(4);

        yield DateTimeField::new('createdAt', '创建时间')
            ->hideOnForm()
            ->setColumns(6);

        yield DateTimeField::new('updatedAt', '更新时间')
            ->hideOnForm()
            ->hideOnIndex()
            ->setColumns(6);
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
            ->add(ChoiceFilter::new('lifecycleStage', '所属阶段')->setChoices($this->lifecycleStageChoices()))
            ->add(ChoiceFilter::new('status', '状态')->setChoices($this->statusChoices()))
            ->add(ChoiceFilter::new('priority', '优先级')->setChoices($this->priorityChoices()))
            ->add(EntityFilter::new('assignee', '负责人'))
            ->add(TextFilter::new('responsibleUnit', '责任单位'))
            ->add(DateTimeFilter::new('startDate', '开始时间'))
            ->add(DateTimeFilter::new('endDate', '结束时间'))
            ->add(DateTimeFilter::new('dueDate', '截止日期'));
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $openStatuses = [
            ProjectTaskStatus::PENDING->value,
            ProjectTaskStatus::IN_PROGRESS->value,
        ];

        return $qb
            ->resetDQLPart('orderBy')
            ->addSelect('(CASE WHEN entity.status IN (:openStatuses) THEN 0 ELSE 1 END) AS HIDDEN taskOpenSort')
            ->addSelect("(CASE WHEN entity.priority = 'high' THEN 0 WHEN entity.priority = 'medium' THEN 1 ELSE 2 END) AS HIDDEN taskPrioritySort")
            ->addOrderBy('taskOpenSort', 'ASC')
            ->addOrderBy('taskPrioritySort', 'ASC')
            ->addOrderBy('entity.dueDate', 'ASC')
            ->addOrderBy('entity.createdAt', 'DESC')
            ->setParameter('openStatuses', $openStatuses);
    }

    public function createEntity(string $entityFqcn): ProjectTask
    {
        $task = new ProjectTask();
        $request = $this->getContext()?->getRequest();
        $projectId = $request?->query->getInt('project') ?? 0;

        if ($projectId > 0) {
            $project = $this->projectRepository->find($projectId);
            if ($project instanceof Project) {
                $task->setProject($project);
            }
        }

        $stage = ProjectLifecycleStage::tryFromKey($request?->query->getString('stage'));
        if ($stage !== null) {
            $task->setLifecycleStage($stage);
        }

        return $task;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof ProjectTask) {
            $this->assertTaskAccess($entityInstance, ProjectVoter::MANAGE);

            $user = $this->getUser();
            if ($user instanceof User) {
                $entityInstance->setCreatedBy($user);
            }
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof ProjectTask) {
            $this->assertTaskAccess($entityInstance, ProjectVoter::MANAGE);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof ProjectTask) {
            $this->assertTaskAccess($entityInstance, ProjectVoter::MANAGE);
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }

    public function detail(AdminContext $context): KeyValueStore|Response
    {
        $task = $context->getEntity()->getInstance();
        if ($task instanceof ProjectTask) {
            $this->assertTaskAccess($task, ProjectVoter::VIEW);
        }

        return parent::detail($context);
    }

    public function edit(AdminContext $context): KeyValueStore|Response
    {
        $task = $context->getEntity()->getInstance();
        if ($task instanceof ProjectTask) {
            $this->assertTaskAccess($task, ProjectVoter::MANAGE);
        }

        return parent::edit($context);
    }

    public function delete(AdminContext $context): KeyValueStore|Response
    {
        $task = $context->getEntity()->getInstance();
        if ($task instanceof ProjectTask) {
            $this->assertTaskAccess($task, ProjectVoter::MANAGE);
        }

        return parent::delete($context);
    }

    public function new(AdminContext $context): KeyValueStore|Response
    {
        $projectId = $context->getRequest()->query->getInt('project');
        if ($projectId > 0) {
            $project = $this->projectRepository->find($projectId);
            if ($project instanceof Project) {
                $this->denyAccessUnlessGranted(ProjectVoter::MANAGE, $project);
            }
        }

        return parent::new($context);
    }

    /**
     * @return array<string, ProjectLifecycleStage>
     */
    private function lifecycleStageChoices(): array
    {
        $choices = [];
        foreach (ProjectLifecycleStage::cases() as $stage) {
            $choices[$stage->label()] = $stage;
        }

        return $choices;
    }

    /**
     * @return array<string, string>
     */
    private function statusChoices(): array
    {
        $choices = [];
        foreach (ProjectTaskStatus::cases() as $status) {
            $choices[$status->label()] = $status;
        }

        return $choices;
    }

    /**
     * @return array<string, string>
     */
    private function priorityChoices(): array
    {
        $choices = [];
        foreach (ProjectTaskPriority::cases() as $priority) {
            $choices[$priority->label()] = $priority;
        }

        return $choices;
    }

    private function assertTaskAccess(ProjectTask $task, string $attribute): void
    {
        $project = $task->getProject();
        if (!$project instanceof Project) {
            throw $this->createAccessDeniedException('任务必须关联到项目。');
        }

        $this->denyAccessUnlessGranted($attribute, $project);
    }
}
