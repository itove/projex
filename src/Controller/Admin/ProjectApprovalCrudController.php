<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProjectApproval;
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

class ProjectApprovalCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProjectApproval::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('立项流程')
            ->setEntityLabelInPlural('立项流程管理')
            ->setPageTitle(Crud::PAGE_INDEX, '立项流程列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建立项流程')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑立项流程')
            ->setPageTitle(Crud::PAGE_DETAIL, '立项流程详情')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['approvingAuthority', 'approvalDocumentNumber']);
    }

    public function configureFields(string $pageName): iterable
    {
        // Project Association
        yield AssociationField::new('project', '所属项目')
            ->setRequired(true)
            ->autocomplete()
            ->setColumns(12);

        // Date Fields
        yield DateField::new('startDate', '立项开始日期')
            ->setRequired(false)
            ->setColumns(6);

        yield DateField::new('completionDate', '立项完成日期')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('完成日期必须晚于开始日期');

        // Authority and Document Fields
        yield TextField::new('approvingAuthority', '审批机关')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('如：国家发改委、省发改委、市发改委等');

        yield TextField::new('approvalDocumentNumber', '批复文号')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('如：发改投资[2026]123号');

        // Files and Images
        yield CollectionField::new('files', '附件文件')
            // ->onlyOnDetail()
            ->setHelp('立项相关的文件：投资批复、用地批复、环评批复等');

        yield CollectionField::new('images', '附件图片')
            ->onlyOnDetail()
            ->setHelp('立项相关的图片：批复文件扫描件等');

        // Detail Fields
        yield TextareaField::new('investmentApprovalDetails', '投资批复说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('项目投资审批的具体内容和要求');

        yield TextareaField::new('landUseApprovalDetails', '用地批复说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('项目用地审批的具体内容和要求');

        yield TextareaField::new('environmentalAssessmentDetails', '环评批复说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('环境影响评估审批的具体内容和要求');

        yield TextareaField::new('socialStabilityAssessmentDetails', '社会稳定风险评估说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('社会稳定风险评估的具体内容和结论');

        yield TextareaField::new('approvalOpinions', '批复意见')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('审批机关的批复意见和要求');

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
