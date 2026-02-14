<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Permission;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class PermissionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Permission::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('权限')
            ->setEntityLabelInPlural('权限')
            ->setPageTitle(Crud::PAGE_INDEX, '权限列表')
            ->setDefaultSort(['module' => 'ASC', 'permissionLevel' => 'ASC'])
            ->setPaginatorPageSize(30);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('permissionCode', '权限编码')->setRequired(true);
        yield TextField::new('permissionName', '权限名称')->setRequired(true);
        yield TextField::new('module', '所属模块')->setRequired(true);
        yield TextField::new('operationType', '操作类型')->setRequired(true);
        yield IntegerField::new('permissionLevel', '权限层级');
        yield TextareaField::new('permissionDescription', '权限描述')->hideOnIndex();
        yield AssociationField::new('parentPermission', '父权限')->autocomplete()->hideOnIndex();
        yield DateTimeField::new('createdAt', '创建时间')->hideOnForm();
        yield DateTimeField::new('updatedAt', '更新时间')->hideOnForm()->hideOnIndex();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}
