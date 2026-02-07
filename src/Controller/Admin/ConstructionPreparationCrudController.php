<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ConstructionPreparation;
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

class ConstructionPreparationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ConstructionPreparation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('施工准备流程')
            ->setEntityLabelInPlural('施工准备流程管理')
            ->setPageTitle(Crud::PAGE_INDEX, '施工准备流程列表')
            ->setPageTitle(Crud::PAGE_NEW, '新建施工准备流程')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑施工准备流程')
            ->setPageTitle(Crud::PAGE_DETAIL, '施工准备流程详情')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['constructionUnit', 'constructionPermitNumber']);
    }

    public function configureFields(string $pageName): iterable
    {
        // Project Association
        yield AssociationField::new('project', '所属项目')
            ->setRequired(true)
            ->autocomplete()
            ->setColumns(12);

        // Date Fields
        yield DateField::new('startDate', '施工准备开始日期')
            ->setRequired(false)
            ->setColumns(6);

        yield DateField::new('completionDate', '施工准备完成日期')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('完成日期必须晚于开始日期');

        // Construction Unit and Permit Fields
        yield TextField::new('constructionUnit', '施工单位')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('如：中国建筑集团、中国中铁等');

        yield TextField::new('constructionPermitNumber', '施工许可证号')
            ->setRequired(false)
            ->setColumns(6)
            ->setHelp('建设工程施工许可证编号');

        // Files and Images
        yield CollectionField::new('files', '附件文件')
            ->setTemplatePath('admin/field/file_collection.html.twig')
            ->setHelp('施工准备相关的文件：施工许可证、施工合同、施工方案等');

        yield CollectionField::new('images', '附件图片')
            ->onlyOnDetail()
            ->setHelp('施工准备相关的图片：施工现场照片、许可证扫描件等');

        // Detail Fields
        yield TextareaField::new('bidDetails', '招标投标说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('招标投标过程和中标结果说明');

        yield TextareaField::new('contractDetails', '施工合同说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('施工合同签订情况及主要条款');

        yield TextareaField::new('constructionPlanDetails', '施工方案说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('施工组织设计和施工方案的主要内容');

        yield TextareaField::new('qualityControlDetails', '质量管理说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('质量管理体系和质量控制措施');

        yield TextareaField::new('safetyPlanDetails', '安全生产方案说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('安全生产管理制度和安全防护措施');

        yield TextareaField::new('environmentalProtectionDetails', '环境保护措施说明')
            ->setRequired(false)
            ->setColumns(12)
            ->hideOnIndex()
            ->setHelp('施工期间环境保护和文明施工措施');

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
