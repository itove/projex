<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PlanningDesign;
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

class PlanningDesignCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PlanningDesign::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('规划设计流程')
            ->setEntityLabelInPlural('规划设计流程管理')
            ->setPageTitle(Crud::PAGE_INDEX, '规划设计流程列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建规划设计流程')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑规划设计流程')
            ->setPageTitle(Crud::PAGE_DETAIL, '规划设计流程详情')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['designUnit', 'designDocumentNumber']);
    }

    public function configureFields(string $pageName): iterable
    {
        // Project Association
        yield AssociationField::new('project', '所属项目')
            ->setRequired(true)
            ->autocomplete()
            ->setColumns(12);

        // Date Fields
        yield DateField::new('startDate', '设计开始日期')
            ->setRequired(false)
            ->setColumns(6);

        yield DateField::new('completionDate', '设计完成日期')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('完成日期必须晚于开始日期');

        // Design Unit and Document Fields
        yield TextField::new('designUnit', '设计单位')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('如：中国建筑设计研究院、省建筑设计院等');

        yield TextField::new('designDocumentNumber', '设计文件编号')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('设计文件编号或图号');

        // Files and Images
        yield CollectionField::new('files', '附件文件')
            ->setTemplatePath('admin/field/file_collection.html.twig')
            ->setHelp('设计相关的文件：初步设计、施工图、概算文件等');

        yield CollectionField::new('images', '附件图片')
            ->onlyOnDetail()
            ->setHelp('设计相关的图片：设计图纸、效果图等');

        // Design Detail Fields
        yield TextareaField::new('preliminaryDesignDetails', '初步设计说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('初步设计文件的主要内容和特点');

        yield TextareaField::new('technicalDesignDetails', '技术设计说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('技术设计文件的主要内容和技术方案');

        yield TextareaField::new('constructionDrawingDetails', '施工图设计说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('施工图设计文件的主要内容和深度');

        yield TextareaField::new('budgetEstimateDetails', '概算说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('设计概算的编制依据和主要内容');

        yield TextareaField::new('designReviewDetails', '设计评审说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('设计评审会议的评审意见和修改要求');

        yield TextareaField::new('designApprovalDetails', '设计批复说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('设计文件批复的主要内容和要求');

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
