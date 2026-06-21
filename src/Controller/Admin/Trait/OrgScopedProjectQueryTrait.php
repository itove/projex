<?php

declare(strict_types=1);

namespace App\Controller\Admin\Trait;

use App\Entity\User;
use App\Service\OrgAccessService;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

trait OrgScopedProjectQueryTrait
{
    abstract protected function getOrgAccessService(): OrgAccessService;

    protected function createOrgScopedProjectIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
        string $projectRelationAlias = 'project',
    ): QueryBuilder {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $user = $this->getUser();
        if (!$user instanceof User) {
            return $qb;
        }

        $qb->join('entity.project', $projectRelationAlias);
        $this->getOrgAccessService()->applyProjectRelationOrgScope($qb, $user, $projectRelationAlias);

        return $qb;
    }
}
