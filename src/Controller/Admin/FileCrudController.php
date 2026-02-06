<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\File;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Vich\UploaderBundle\Form\Type\VichFileType;

class FileCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return File::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('文件')
            ->setEntityLabelInPlural('文件管理')
            ->setPageTitle(Crud::PAGE_INDEX, '文件管理')
            ->setPageTitle(Crud::PAGE_NEW, '上传文件')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑文件')
            ->setPageTitle(Crud::PAGE_DETAIL, '文件详情')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['originalName', 'fileName', 'description', 'category']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('file', '文件')
            ->setFormType(VichFileType::class)
            ->onlyOnForms()
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->setHelp('最大文件大小: 50MB');

        yield AssociationField::new('preliminaryDecision', '所属前期决策')
            ->autocomplete()
            ->setRequired(false)
            ->setColumns(6);

        yield TextField::new('originalName', '原始文件名')
            ->hideOnForm()
            ->setColumns(6);

        yield TextField::new('fileName', '存储文件名')
            ->hideOnForm()
            ->hideOnIndex()
            ->setColumns(6);

        yield TextField::new('mimeType', '文件类型')
            ->hideOnForm()
            ->setColumns(4);

        yield IntegerField::new('fileSize', '文件大小')
            ->hideOnForm()
            ->hideOnIndex()
            ->setColumns(4);

        yield TextField::new('fileSizeFormatted', '文件大小')
            ->onlyOnDetail()
            ->setColumns(4);

        yield TextField::new('category', '分类')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('例如: 合同文件、技术文档、财务报表等');

        yield TextareaField::new('description', '描述')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex();

        yield DateTimeField::new('createdAt', '上传时间')
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
            ->add(TextFilter::new('category', '分类'))
            ->add(TextFilter::new('mimeType', '文件类型'))
            ->add(DateTimeFilter::new('createdAt', '上传时间'));
    }
}
