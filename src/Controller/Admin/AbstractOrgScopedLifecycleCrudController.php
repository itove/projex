<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\OrgAccessService;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use App\Controller\Admin\Trait\OrgScopedProjectQueryTrait;

abstract class AbstractOrgScopedLifecycleCrudController extends AbstractCrudController
{
    use OrgScopedProjectQueryTrait;

    public function __construct(
        protected readonly OrgAccessService $orgAccessService,
    ) {
    }

    protected function getOrgAccessService(): OrgAccessService
    {
        return $this->orgAccessService;
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        return $this->createOrgScopedProjectIndexQueryBuilder(
            $searchDto,
            $entityDto,
            $fields,
            $filters,
        );
    }
}
