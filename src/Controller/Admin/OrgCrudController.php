<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Org;
use App\Entity\User;
use App\Service\OrgAccessService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class OrgCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly OrgAccessService $orgAccessService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Org::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('组织机构')
            ->setEntityLabelInPlural('组织机构')
            ->setPageTitle(Crud::PAGE_INDEX, '组织机构列表')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', '组织名称')->setRequired(true);
        yield TextField::new('orgCode', '组织编码')->setRequired(true);
        yield AssociationField::new('parent', '上级组织')
            ->autocomplete()
            ->setRequired(false)
            ->setHelp('每个组织最多有一个上级组织');
        yield AssociationField::new('children', '下级组织')
            ->onlyOnDetail()
            ->setHelp('该组织的直属下级组织');
        yield TextareaField::new('description', '组织描述')->hideOnIndex();
        yield TextField::new('contactPerson', '联系人')->hideOnIndex();
        yield TextField::new('contactPhone', '联系电话')->hideOnIndex();
        yield TextField::new('address', '地址')->hideOnIndex();
        yield DateTimeField::new('createdAt', '创建时间')->hideOnForm();
        yield DateTimeField::new('updatedAt', '更新时间')->hideOnForm()->hideOnIndex();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $qb;
        }

        $accessibleOrgIds = $this->orgAccessService->getAccessibleOrgIds($user);
        if ($accessibleOrgIds === null) {
            return $qb;
        }

        if ($accessibleOrgIds === []) {
            $qb->andWhere('1 = 0');

            return $qb;
        }

        $qb->andWhere('entity.id IN (:accessibleOrgIds)')
            ->setParameter('accessibleOrgIds', $accessibleOrgIds);

        return $qb;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Org) {
            $this->assertValidParent($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Org) {
            $this->assertValidParent($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function assertValidParent(Org $org): void
    {
        if ($this->orgAccessService->wouldCreateCycle($org, $org->getParent())) {
            throw $this->createAccessDeniedException('不能将组织设置为其自身或其下级组织的上级。');
        }
    }
}
