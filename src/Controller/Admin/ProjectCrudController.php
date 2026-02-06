<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Entity\ProjectType;
use App\Enum\FundingSource;
use App\Enum\ProjectNature;
use App\Enum\ProjectStatus;
use App\Service\ProjectLockingService;
use App\Service\ProjectNumberGenerator;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProjectCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ProjectNumberGenerator $numberGenerator,
        private readonly ProjectLockingService $lockingService,
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
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
            ->setSearchFields(['projectName', 'projectNumber', 'leaderName', 'projectLocation']);
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
            ->setHelp('先选择项目类型，然后选择对应的子类型');

        yield TextField::new('projectIndustry', '项目行业')
            ->setRequired(true)
            ->setColumns(6);

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
            ->formatValue(fn($value) => $value?->label());

        // Project Leader Section
        yield TextField::new('leaderName', '负责人姓名')
            ->setRequired(true)
            ->setColumns(4);

        yield TextField::new('leaderPhone', '负责人电话')
            ->setRequired(true)
            ->setColumns(4);

        yield EmailField::new('leaderEmail', '负责人邮箱')
            ->setRequired(false)
            ->setColumns(4);

        // Project Parameters Section
        yield MoneyField::new('budget', '项目预算')
            ->setCurrency('CNY')
            ->setRequired(true)
            ->setColumns(6)
            ->setFormTypeOption('disabled', $lockCoreFields)
            ->setHelp('单位：元');

        yield ChoiceField::new('fundingSource', '资金来源')
            ->setChoices(array_combine(
                array_map(fn(FundingSource $source) => $source->label(), FundingSource::cases()),
                FundingSource::cases()
            ))
            ->setRequired(true)
            ->setColumns(6)
            ->setFormTypeOption('disabled', $lockCoreFields)
            ->formatValue(fn($value) => $value?->label());

        yield DateField::new('plannedStartDate', '计划开始日期')
            ->setRequired(true)
            ->setColumns(6)
            ->setFormTypeOption('disabled', $lockCoreFields);

        yield DateField::new('plannedEndDate', '计划结束日期')
            ->setRequired(true)
            ->setColumns(6)
            ->setFormTypeOption('disabled', $lockCoreFields);

        yield TextareaField::new('purpose', '项目目的')
            ->setRequired(true)
            ->setColumns(12);

        yield TextareaField::new('scale', '项目规模')
            ->setRequired(true)
            ->setColumns(12);

        // Registrant Info Section
        yield TextField::new('registrantName', '登记人姓名')
            ->setRequired(true)
            ->setColumns(4);

        yield TextField::new('registrantOrganization', '登记人单位')
            ->setRequired(true)
            ->setColumns(4);

        yield TextField::new('registrantPhone', '登记人电话')
            ->setRequired(true)
            ->setColumns(4);

        // Optional Fields Section
        yield TextareaField::new('remarks', '备注')
            ->setRequired(false)
            ->setColumns(12);

        yield TextareaField::new('specialNotes', '特殊说明')
            ->setRequired(false)
            ->setColumns(12);

        yield TextareaField::new('prerequisiteNotes', '前置条件说明')
            ->setRequired(false)
            ->setColumns(12);

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
            ->setColumns(3);
    }

    public function configureActions(Actions $actions): Actions
    {
        $submitAction = Action::new('submitForReview', '提交审核', 'fa fa-check')
            ->linkToCrudAction('submitForReview')
            ->displayIf(fn(Project $project) => $project->getStatus() === ProjectStatus::DRAFT);

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, $submitAction)
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
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
            ->add(DateTimeFilter::new('createdAt', '创建时间'));
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Project) {
            $entityInstance->setStatus(ProjectStatus::DRAFT);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function submitForReview(AdminContext $context): RedirectResponse
    {
        $project = $context->getEntity()->getInstance();

        if (!$project instanceof Project) {
            $this->addFlash('error', '无效的项目实例');
            return $this->redirect($this->adminUrlGenerator->setAction(Action::INDEX)->generateUrl());
        }

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
}
