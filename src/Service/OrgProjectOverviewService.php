<?php

declare(strict_types=1);

namespace App\Service;

use App\Controller\Admin\ProjectCrudController;
use App\DTO\OrgOverviewNode;
use App\Entity\Org;
use App\Entity\User;
use App\Repository\OrgRepository;
use App\Repository\ProjectRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class OrgProjectOverviewService
{
    public function __construct(
        private readonly OrgRepository $orgRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly OrgAccessService $orgAccessService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    /**
     * @return list<OrgOverviewNode>
     */
    public function getTree(?User $user): array
    {
        $accessibleOrgIds = $this->orgAccessService->getAccessibleOrgIds($user);
        if ($accessibleOrgIds === []) {
            return [];
        }

        $orgs = $accessibleOrgIds === null
            ? $this->orgRepository->findBy([], ['name' => 'ASC'])
            : $this->orgRepository->findBy(['id' => $accessibleOrgIds], ['name' => 'ASC']);

        if ($orgs === []) {
            return [];
        }

        $accessibleIdSet = array_flip(array_map(
            static fn (Org $org): int => (int) $org->getId(),
            $orgs,
        ));

        $directCounts = $this->fetchDirectProjectCounts(array_keys($accessibleIdSet));
        $nodesById = [];

        foreach ($orgs as $org) {
            $orgId = (int) $org->getId();
            $nodesById[$orgId] = new OrgOverviewNode(
                id: $orgId,
                name: $org->getName() ?? '',
                orgCode: $org->getOrgCode() ?? '',
                contactPerson: $org->getContactPerson(),
                directProjectCount: $directCounts[$orgId] ?? 0,
                totalProjectCount: $directCounts[$orgId] ?? 0,
                projectListUrl: $this->buildProjectListUrl($orgId),
            );
        }

        $roots = [];

        foreach ($orgs as $org) {
            $orgId = (int) $org->getId();
            $parentId = $org->getParent()?->getId();
            $node = $nodesById[$orgId];

            if ($parentId !== null && isset($accessibleIdSet[$parentId])) {
                $nodesById[$parentId]->children[] = $node;
                continue;
            }

            $roots[] = $node;
        }

        foreach ($roots as $root) {
            $this->rollupTotalCounts($root);
        }

        return $roots;
    }

    /**
     * @param list<int> $orgIds
     *
     * @return array<int, int>
     */
    private function fetchDirectProjectCounts(array $orgIds): array
    {
        if ($orgIds === []) {
            return [];
        }

        $rows = $this->projectRepository->createQueryBuilder('p')
            ->select('IDENTITY(p.org) AS orgId, COUNT(p.id) AS projectCount')
            ->where('p.org IN (:orgIds)')
            ->setParameter('orgIds', $orgIds)
            ->groupBy('p.org')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['orgId']] = (int) $row['projectCount'];
        }

        return $counts;
    }

    private function buildProjectListUrl(int $orgId): string
    {
        return $this->adminUrlGenerator
            ->setController(ProjectCrudController::class)
            ->setAction(Action::INDEX)
            ->set('orgId', (string) $orgId)
            ->generateUrl();
    }

    private function rollupTotalCounts(OrgOverviewNode $node): int
    {
        $total = $node->directProjectCount;

        foreach ($node->children as $child) {
            $total += $this->rollupTotalCounts($child);
        }

        $node->totalProjectCount = $total;

        return $total;
    }
}
