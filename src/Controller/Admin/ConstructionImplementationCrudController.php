<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ConstructionImplementation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class ConstructionImplementationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ConstructionImplementation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('施工实施流程')
            ->setEntityLabelInPlural('施工实施流程管理')
            ->setPageTitle(Crud::PAGE_INDEX, '施工实施流程列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建施工实施流程')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑施工实施流程')
            ->setPageTitle(Crud::PAGE_DETAIL, '施工实施流程详情')
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

        // Date Fields
        yield DateField::new('startDate', '施工开始日期')
            ->setRequired(false)
            ->setColumns(6);

        yield DateField::new('completionDate', '施工完成日期')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('完成日期必须晚于开始日期');

        // Progress Field
        yield IntegerField::new('currentProgress', '当前实施进度（%）')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('施工实施进度百分比（0-100）')
            ->setFormTypeOption('attr', ['min' => 0, 'max' => 100]);

        // Files and Images
        yield CollectionField::new('files', '附件文件')
            // ->setColumns(12)
            // ->onlyOnDetail()
            ->setHelp('施工实施相关的文件：施工日志、检验报告、监理报告等');

        yield CollectionField::new('images', '附件图片')
            ->onlyOnDetail()
            ->setHelp('施工实施相关的图片：施工现场照片、质量问题照片等');

        // Detail Fields
        yield TextareaField::new('constructionProgressDetails', '施工进度说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('各阶段施工进度情况和工期控制');

        yield TextareaField::new('qualityInspectionDetails', '质量检验记录')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('施工过程中的质量检验和验收记录');

        yield TextareaField::new('safetyInspectionDetails', '安全检查记录')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('施工期间安全检查和隐患整改记录');

        yield TextareaField::new('progressPaymentDetails', '进度款支付记录')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('按施工进度支付工程款的记录');

        yield TextareaField::new('changeOrderDetails', '变更签证记录')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('工程变更和现场签证的记录');

        yield TextareaField::new('supervisionDetails', '监理工作记录')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('监理单位的监理日志和工作记录');

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
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
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
