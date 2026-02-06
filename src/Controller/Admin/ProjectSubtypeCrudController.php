<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProjectSubtype;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class ProjectSubtypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProjectSubtype::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('项目子类型')
            ->setEntityLabelInPlural('项目子类型')
            ->setPageTitle(Crud::PAGE_INDEX, '项目子类型管理')
            ->setPageTitle(Crud::PAGE_NEW, '新建项目子类型')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑项目子类型')
            ->setPageTitle(Crud::PAGE_DETAIL, '项目子类型详情')
            ->setDefaultSort(['sortOrder' => 'ASC', 'name' => 'ASC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['code', 'name', 'description']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('projectType', '所属项目类型')
            ->setRequired(true)
            ->setColumns(6)
            ->autocomplete();

        yield TextField::new('code', '子类型代码')
            ->setRequired(true)
            ->setColumns(6)
            ->setHelp('仅小写字母和下划线，如: municipal_engineering, building_construction');

        yield TextField::new('name', '子类型名称')
            ->setRequired(true)
            ->setColumns(6);

        yield TextareaField::new('description', '描述')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex();

        yield IntegerField::new('sortOrder', '排序')
            ->setColumns(6)
            ->setHelp('数字越小越靠前');

        yield BooleanField::new('isActive', '启用')
            ->setColumns(6);

        yield DateTimeField::new('createdAt', '创建时间')
            ->hideOnForm()
            ->setColumns(6);

        yield DateTimeField::new('updatedAt', '更新时间')
            ->hideOnForm()
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
            ->add(EntityFilter::new('projectType', '项目类型'));
    }
}
