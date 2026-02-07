<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\CompletionAcceptance;
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

class CompletionAcceptanceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CompletionAcceptance::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('竣工验收流程')
            ->setEntityLabelInPlural('竣工验收流程管理')
            ->setPageTitle(Crud::PAGE_INDEX, '竣工验收流程列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建竣工验收流程')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑竣工验收流程')
            ->setPageTitle(Crud::PAGE_DETAIL, '竣工验收流程详情')
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

        // Acceptance Date
        yield DateField::new('acceptanceDate', '验收日期')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('项目通过竣工验收的日期');

        // Files and Images
        yield CollectionField::new('files', '附件文件')
            ->setTemplatePath('admin/field/file_collection.html.twig')
            ->setHelp('竣工验收相关的文件：竣工报告、验收证书、质量评定报告等');

        yield CollectionField::new('images', '附件图片')
            ->onlyOnDetail()
            ->setHelp('竣工验收相关的图片：验收现场照片、工程照片等');

        // Detail Fields
        yield TextareaField::new('completionReportDetails', '竣工报告内容')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('竣工报告的主要内容，包括工程概况、建设过程、质量评价等');

        yield TextareaField::new('qualityEvaluationDetails', '质量评定结果')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('工程质量评定的结果，包括分部分项工程质量、整体质量等级等');

        yield TextareaField::new('acceptanceInspectionDetails', '验收检查记录')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('验收过程中的检查记录，包括现场检查、资料检查等');

        yield TextareaField::new('defectRectificationDetails', '缺陷整改记录')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('验收发现缺陷的整改情况记录');

        yield TextareaField::new('finalAccountsDetails', '竣工决算说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('竣工财务决算的情况说明，包括总投资、资金使用等');

        yield TextareaField::new('archiveDocumentationDetails', '档案资料移交')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('竣工档案资料的整理移交情况');

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
            ->add(DateTimeFilter::new('acceptanceDate', '验收日期'))
            ->add(DateTimeFilter::new('createdAt', '创建时间'));
    }
}
