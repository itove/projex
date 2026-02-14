<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Role;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class RoleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Role::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('角色')
            ->setEntityLabelInPlural('角色')
            ->setPageTitle(Crud::PAGE_INDEX, '角色列表')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('roleCode', '角色编码')->setRequired(true);
        yield TextField::new('roleName', '角色名称')->setRequired(true);
        yield TextareaField::new('roleDescription', '角色描述')->hideOnIndex();
        yield BooleanField::new('isPreset', '预设角色')->renderAsSwitch(false);
        yield AssociationField::new('permissions', '权限')->autocomplete();
        yield DateTimeField::new('createdAt', '创建时间')->hideOnForm();
        yield DateTimeField::new('updatedAt', '更新时间')->hideOnForm()->hideOnIndex();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}
