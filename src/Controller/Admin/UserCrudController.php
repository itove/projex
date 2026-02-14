<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('用户')
            ->setEntityLabelInPlural('用户')
            ->setPageTitle(Crud::PAGE_INDEX, '用户列表')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('username', '用户名')->setRequired(true);
        yield TextField::new('name', '姓名')->setRequired(true);
        yield AssociationField::new('org', '所属组织')->setRequired(true)->autocomplete();
        yield AssociationField::new('userRoles', '角色')->autocomplete();
        yield TextField::new('phone', '手机号');
        yield EmailField::new('email', '邮箱');
        yield TextField::new('position', '职位')->hideOnIndex();
        yield BooleanField::new('isActive', '激活状态')->renderAsSwitch(false);
        yield DateTimeField::new('lastLoginAt', '最后登录时间')->hideOnForm()->hideOnIndex();
        yield TextField::new('lastLoginIp', '最后登录IP')->hideOnForm()->hideOnIndex();
        yield DateTimeField::new('createdAt', '创建时间')->hideOnForm();
        yield DateTimeField::new('updatedAt', '更新时间')->hideOnForm()->hideOnIndex();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
    }
}
