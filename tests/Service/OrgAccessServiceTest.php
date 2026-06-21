<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Org;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\OrgRepository;
use App\Service\OrgAccessService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class OrgAccessServiceTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private OrgRepository $orgRepository;
    private OrgAccessService $orgAccessService;
    private Security $security;
    private TokenStorageInterface $tokenStorage;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->orgRepository = $container->get(OrgRepository::class);
        $this->orgAccessService = $container->get(OrgAccessService::class);
        $this->security = $container->get(Security::class);
        $this->tokenStorage = $container->get(TokenStorageInterface::class);
    }

    public function testParentOrgUserCanAccessSubsidiaryProjects(): void
    {
        $parentOrg = $this->orgRepository->findOneBy(['orgCode' => 'ORG-SZGC-001']);
        $childOrg = $this->orgRepository->findOneBy(['orgCode' => 'ORG-ZHCS-002']);
        $grandchildOrg = $this->orgRepository->findOneBy(['orgCode' => 'ORG-JTSH-004']);

        $this->assertNotNull($parentOrg);
        $this->assertNotNull($childOrg);
        $this->assertNotNull($grandchildOrg);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'pm_zhang']);
        $this->assertNotNull($user);
        $this->assertSame($parentOrg->getId(), $user->getOrg()?->getId());

        $this->loginAs($user);

        $accessibleOrgIds = $this->orgAccessService->getAccessibleOrgIds($user);
        $this->assertContains($parentOrg->getId(), $accessibleOrgIds);
        $this->assertContains($childOrg->getId(), $accessibleOrgIds);
        $this->assertContains($grandchildOrg->getId(), $accessibleOrgIds);

        $childProject = $this->entityManager->getRepository(Project::class)->findOneBy(['org' => $childOrg]);
        $this->assertNotNull($childProject);
        $this->assertTrue($this->orgAccessService->canViewProject($user, $childProject));
    }

    public function testChildOrgUserCannotAccessParentOrgProjects(): void
    {
        $parentOrg = $this->orgRepository->findOneBy(['orgCode' => 'ORG-SZGC-001']);
        $childOrg = $this->orgRepository->findOneBy(['orgCode' => 'ORG-ZHCS-002']);

        $this->assertNotNull($parentOrg);
        $this->assertNotNull($childOrg);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'pm_li']);
        $this->assertNotNull($user);
        $this->assertSame($childOrg->getId(), $user->getOrg()?->getId());

        $this->loginAs($user);

        $accessibleOrgIds = $this->orgAccessService->getAccessibleOrgIds($user);
        $this->assertContains($childOrg->getId(), $accessibleOrgIds);
        $this->assertNotContains($parentOrg->getId(), $accessibleOrgIds);

        $parentProject = $this->entityManager->getRepository(Project::class)->findOneBy(['org' => $parentOrg]);
        $this->assertNotNull($parentProject);
        $this->assertFalse($this->orgAccessService->canViewProject($user, $parentProject));
    }

    public function testChildOrgUserCanAccessOwnSubsidiaryProjects(): void
    {
        $childOrg = $this->orgRepository->findOneBy(['orgCode' => 'ORG-ZHCS-002']);
        $grandchildOrg = $this->orgRepository->findOneBy(['orgCode' => 'ORG-JTSH-004']);

        $this->assertNotNull($childOrg);
        $this->assertNotNull($grandchildOrg);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'pm_li']);
        $this->assertNotNull($user);

        $this->loginAs($user);

        $accessibleOrgIds = $this->orgAccessService->getAccessibleOrgIds($user);
        $this->assertContains($grandchildOrg->getId(), $accessibleOrgIds);

        $grandchildProject = $this->entityManager->getRepository(Project::class)->findOneBy(['org' => $grandchildOrg]);
        $this->assertNotNull($grandchildProject);
        $this->assertTrue($this->orgAccessService->canViewProject($user, $grandchildProject));
    }

    public function testAuditorCanViewButNotManageWithinOrgScope(): void
    {
        $childOrg = $this->orgRepository->findOneBy(['orgCode' => 'ORG-ZHCS-002']);
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'auditor2']);
        $project = $this->entityManager->getRepository(Project::class)->findOneBy(['org' => $childOrg]);

        $this->assertNotNull($user);
        $this->assertNotNull($project);

        $this->loginAs($user);

        $this->assertTrue($this->orgAccessService->canViewProject($user, $project));
        $this->assertFalse($this->orgAccessService->canManageProject($user, $project));
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
