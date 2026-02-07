<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SettlementAccounts;
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
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class SettlementAccountsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SettlementAccounts::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('结算流程')
            ->setEntityLabelInPlural('结算流程管理')
            ->setPageTitle(Crud::PAGE_INDEX, '结算流程列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建结算流程')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑结算流程')
            ->setPageTitle(Crud::PAGE_DETAIL, '结算流程详情')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        // Project Association
        yield AssociationField::new('project', '所属项目')
            ->setRequired(true)
            ->autocomplete()
            ->setColumns(12);

        // Settlement Date
        yield DateField::new('settlementDate', '结算日期')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('项目工程结算完成的日期');

        // Files and Images
        yield CollectionField::new('files', '附件文件')
            ->setTemplatePath('admin/field/file_collection.html.twig')
            ->setHelp('结算相关的文件：结算书、审核报告、支付凭证等');

        yield CollectionField::new('images', '附件图片')
            ->onlyOnDetail()
            ->setHelp('结算相关的图片：结算资料照片等');

        // Detail Fields
        yield TextareaField::new('contractSettlementDetails', '合同结算明细')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('合同价款结算的详细情况，包括合同价、变更价、索赔价等');

        yield TextareaField::new('costAuditDetails', '造价审核结果')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('工程造价审核的结果，包括审定价格、核减情况等');

        yield TextareaField::new('quantitySurveyDetails', '工程量核定说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('工程量核定的过程和结果说明');

        yield TextareaField::new('disputeResolutionDetails', '争议处理记录')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('结算过程中争议的处理情况记录');

        yield TextareaField::new('paymentCompletionDetails', '款项支付完成情况')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('工程款项最终支付完成的情况说明');

        yield TextareaField::new('warrantyManagementDetails', '质保金管理')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('质保金的留存、管理和返还情况');

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
            ->add(DateTimeFilter::new('settlementDate', '结算日期'))
            ->add(DateTimeFilter::new('createdAt', '创建时间'));
    }
}
