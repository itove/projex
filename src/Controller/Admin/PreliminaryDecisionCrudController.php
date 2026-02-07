<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PreliminaryDecision;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class PreliminaryDecisionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PreliminaryDecision::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('前期决策流程')
            ->setEntityLabelInPlural('前期决策流程管理')
            ->setPageTitle(Crud::PAGE_INDEX, '前期决策流程列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建前期决策流程')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑前期决策流程')
            ->setPageTitle(Crud::PAGE_DETAIL, '前期决策流程详情')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['organizingUnit', 'feasibilityStudyOrganization']);
    }

    public function configureFields(string $pageName): iterable
    {
        // Project Association
        yield AssociationField::new('project', '所属项目')
            ->setRequired(true)
            ->autocomplete()
            ->setColumns(12);

        // Date Fields
        yield DateField::new('startDate', '开始日期')
            ->setRequired(false)
            ->setColumns(6);

        yield DateField::new('completionDate', '完成日期')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('完成日期必须晚于开始日期');

        // Organization Fields
        yield TextField::new('organizingUnit', '组织单位')
            ->setRequired(false)
            ->setColumns(6);

        yield TextField::new('feasibilityStudyOrganization', '可研编制单位')
            ->setRequired(false)
            ->setColumns(6);

        // Files and Images
        yield CollectionField::new('files', '附件文件')
            // ->onlyOnDetail()
            ->setHelp('前期决策相关的文件：项目建议书、可行性研究报告、审批文件等');

        yield CollectionField::new('images', '附件图片')
            ->onlyOnDetail()
            ->setHelp('前期决策相关的图片：资质证明、审批文件扫描件等');

        // Detail Fields
        yield TextareaField::new('projectProposalDetails', '项目建议书说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex();

        yield TextareaField::new('feasibilityStudyDetails', '可行性研究报告说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex();

        yield TextareaField::new('fundingArrangementDetails', '资金筹措方案说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex();

        yield TextareaField::new('approvalOpinions', '审批意见')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex();

        // System Fields
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
        $reviewAction = Action::new('review', '审核')
            ->linkToCrudAction('reviewStage')
            ->setCssClass('btn btn-primary')
            ->displayAsButton();

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $reviewAction)
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    public function reviewStage(AdminContext $context): Response
    {
        // TODO: Implement review logic
        $entity = $context->getEntity()->getInstance();

        $this->addFlash('info', '审核功能开发中');

        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($entity->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('project', '项目'))
            ->add(DateTimeFilter::new('startDate', '开始日期'))
            ->add(DateTimeFilter::new('completionDate', '完成日期'))
            ->add(DateTimeFilter::new('createdAt', '创建时间'));
    }
}
