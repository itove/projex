<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Org;
use App\Entity\User;
use App\Repository\OrgRepository;
use App\Service\OrgProjectOverviewService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class OrgProjectOverviewServiceTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private OrgRepository $orgRepository;
    private OrgProjectOverviewService $overviewService;
    private TokenStorageInterface $tokenStorage;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->orgRepository = $container->get(OrgRepository::class);
        $this->overviewService = $container->get(OrgProjectOverviewService::class);
        $this->tokenStorage = $container->get(TokenStorageInterface::class);
    }

    public function testParentOrgUserSeesFullSubtreeWithRolledUpCounts(): void
    {
        $parentOrg = $this->orgRepository->findOneBy(['orgCode' => 'ORG-SZGC-001']);
        $childOrg = $this->orgRepository->findOneBy(['orgCode' => 'ORG-ZHCS-002']);
        $grandchildOrg = $this->orgRepository->findOneBy(['orgCode' => 'ORG-JTSH-004']);

        $this->assertNotNull($parentOrg);
        $this->assertNotNull($childOrg);
        $this->assertNotNull($grandchildOrg);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'pm_zhang']);
        $this->assertNotNull($user);

        $this->loginAs($user);

        $tree = $this->overviewService->getTree($user);
        $this->assertNotEmpty($tree);

        $root = $this->findNodeByOrgCode($tree, 'ORG-SZGC-001');
        $this->assertNotNull($root);
        $this->assertGreaterThan(0, $root->totalProjectCount);
        $this->assertGreaterThanOrEqual($root->directProjectCount, $root->totalProjectCount);

        $childNode = $this->findNodeByOrgCode($tree, 'ORG-ZHCS-002');
        $this->assertNotNull($childNode);
        $this->assertNotEmpty($childNode->children);

        $grandchildNode = $this->findNodeByOrgCode($tree, 'ORG-JTSH-004');
        $this->assertNotNull($grandchildNode);
        $this->assertGreaterThanOrEqual(
            $grandchildNode->directProjectCount,
            $childNode->totalProjectCount,
        );
    }

    public function testChildOrgUserDoesNotSeeParentInTree(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'pm_li']);
        $this->assertNotNull($user);

        $this->loginAs($user);

        $tree = $this->overviewService->getTree($user);
        $this->assertNotEmpty($tree);

        $this->assertNull($this->findNodeByOrgCode($tree, 'ORG-SZGC-001'));
        $this->assertNotNull($this->findNodeByOrgCode($tree, 'ORG-ZHCS-002'));
    }

    public function testProjectListUrlContainsOrgId(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'pm_li']);
        $this->assertNotNull($user);

        $this->loginAs($user);

        $tree = $this->overviewService->getTree($user);
        $node = $this->findNodeByOrgCode($tree, 'ORG-ZHCS-002');

        $this->assertNotNull($node);
        $this->assertStringContainsString('orgId=', $node->projectListUrl);
        $this->assertStringContainsString((string) $node->id, $node->projectListUrl);
    }

    public function testOrgNodeIncludesDirectProjects(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'pm_li']);
        $this->assertNotNull($user);

        $this->loginAs($user);

        $tree = $this->overviewService->getTree($user);
        $node = $this->findNodeByOrgCode($tree, 'ORG-ZHCS-002');

        $this->assertNotNull($node);
        $this->assertCount($node->directProjectCount, $node->projects);

        if ($node->projects !== []) {
            $project = $node->projects[0];
            $this->assertNotEmpty($project->name);
            $this->assertStringContainsString('/admin/project/', $project->detailUrl);
        }
    }

    /**
     * @param list<\App\DTO\OrgOverviewNode> $nodes
     */
    private function findNodeByOrgCode(array $nodes, string $orgCode): ?\App\DTO\OrgOverviewNode
    {
        foreach ($nodes as $node) {
            if ($node->orgCode === $orgCode) {
                return $node;
            }

            $found = $this->findNodeByOrgCode($node->children, $orgCode);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    private function loginAs(User $user): void
    {
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
    }

    protected function tearDown(): void
    {
        $this->tokenStorage->setToken(null);
        parent::tearDown();
        $this->entityManager->close();
    }
}
