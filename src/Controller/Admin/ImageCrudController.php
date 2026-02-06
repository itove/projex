<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Image;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Image::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('图片')
            ->setEntityLabelInPlural('图片管理')
            ->setPageTitle(Crud::PAGE_INDEX, '图片管理')
            ->setPageTitle(Crud::PAGE_NEW, '上传图片')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑图片')
            ->setPageTitle(Crud::PAGE_DETAIL, '图片详情')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['originalName', 'fileName', 'caption', 'description', 'category']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('imageFile', '图片')
            ->setFormType(VichImageType::class)
            ->onlyOnForms()
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->setHelp('支持格式: JPEG, PNG, GIF, WebP。最大大小: 10MB');

        yield AssociationField::new('preliminaryDecision', '所属前期决策')
            ->autocomplete()
            ->setRequired(false)
            ->setColumns(6);

        yield AssociationField::new('projectApproval', '所属立项流程')
            ->autocomplete()
            ->setRequired(false)
            ->setColumns(6);

        yield AssociationField::new('planningDesign', '所属规划设计')
            ->autocomplete()
            ->setRequired(false)
            ->setColumns(6);

        yield AssociationField::new('constructionPreparation', '所属施工准备')
            ->autocomplete()
            ->setRequired(false)
            ->setColumns(6);

        yield AssociationField::new('constructionImplementation', '所属施工实施')
            ->autocomplete()
            ->setRequired(false)
            ->setColumns(6);

        yield ImageField::new('fileName', '预览')
            ->setBasePath('/uploads/images')
            ->hideOnForm()
            ->setColumns(3);

        yield TextField::new('originalName', '原始文件名')
            ->hideOnForm()
            ->setColumns(6);

        yield TextField::new('caption', '标题')
            ->setRequired(false)
            ->setColumns(6);

        yield TextField::new('altText', 'Alt文本')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('用于无障碍访问和SEO优化');

        yield TextField::new('dimensionsFormatted', '尺寸')
            ->onlyOnDetail()
            ->setColumns(4);

        yield TextField::new('fileSizeFormatted', '文件大小')
            ->onlyOnDetail()
            ->setColumns(4);

        yield TextField::new('mimeType', '文件类型')
            ->hideOnForm()
            ->hideOnIndex()
            ->setColumns(4);

        yield TextField::new('category', '分类')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('例如: 项目图片、现场照片、效果图等');

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
            ->add(DateTimeFilter::new('createdAt', '上传时间'));
    }
}
