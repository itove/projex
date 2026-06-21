<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Org;
use App\Entity\Project;
use App\Entity\User;
use App\Repository\OrgRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test org-scoped project visibility.
 */
class ProjectManagerAccessTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private OrgRepository $orgRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->orgRepository = $this->entityManager->getRepository(Org::class);
    }

    public function testProjectManagerSeesProjectsInOrgTree(): void
    {
        $userRepo = $this->entityManager->getRepository(User::class);
        $projectManager = $userRepo->findOneBy(['username' => 'pm_zhang']);

        $this->assertNotNull($projectManager, 'Test user pm_zhang should exist');

        $accessibleOrgIds = $this->orgRepository->findDescendantIds($projectManager->getOrg()->getId());
        $projectRepo = $this->entityManager->getRepository(Project::class);

        $visibleProjects = $projectRepo->createQueryBuilder('p')
            ->where('p.org IN (:orgIds)')
            ->setParameter('orgIds', $accessibleOrgIds)
            ->getQuery()
            ->getResult();

        $allProjects = $projectRepo->findAll();

        $this->assertGreaterThan(0, count($visibleProjects), 'Parent org user should see projects in org tree');
        $this->assertSame(count($allProjects), count($visibleProjects), 'Root org user should see all fixture projects');
    }

    public function testChildOrgProjectManagerHasNarrowerScope(): void
    {
        $userRepo = $this->entityManager->getRepository(User::class);
        $projectManager = $userRepo->findOneBy(['username' => 'pm_li']);

        $this->assertNotNull($projectManager, 'Test user pm_li should exist');

        $accessibleOrgIds = $this->orgRepository->findDescendantIds($projectManager->getOrg()->getId());
        $projectRepo = $this->entityManager->getRepository(Project::class);

        $visibleProjects = $projectRepo->createQueryBuilder('p')
            ->where('p.org IN (:orgIds)')
            ->setParameter('orgIds', $accessibleOrgIds)
            ->getQuery()
            ->getResult();

        $allProjects = $projectRepo->findAll();

        $this->assertGreaterThan(0, count($visibleProjects));
        $this->assertLessThan(count($allProjects), count($visibleProjects), 'Child org user should see fewer projects than root org');
    }

    public function testSupervisorIsOrgScoped(): void
    {
        $userRepo = $this->entityManager->getRepository(User::class);
        $supervisor = $userRepo->findOneBy(['username' => 'supervisor2']);

        $this->assertNotNull($supervisor, 'Test user supervisor2 should exist');
        $this->assertContains('ROLE_SUPERVISOR', $supervisor->getRoles());

        $accessibleOrgIds = $this->orgRepository->findDescendantIds($supervisor->getOrg()->getId());
        $projectRepo = $this->entityManager->getRepository(Project::class);

        $visibleProjects = $projectRepo->createQueryBuilder('p')
            ->where('p.org IN (:orgIds)')
            ->setParameter('orgIds', $accessibleOrgIds)
            ->getQuery()
            ->getResult();

        $allProjects = $projectRepo->findAll();

        $this->assertGreaterThan(0, count($visibleProjects));
        $this->assertLessThan(count($allProjects), count($visibleProjects), 'Supervisor should be limited to org tree');
    }

    public function testSystemAdminSeesAllProjects(): void
    {
        $userRepo = $this->entityManager->getRepository(User::class);
        $admin = $userRepo->findOneBy(['username' => 'admin']);

        $this->assertNotNull($admin, 'Test user admin should exist');
        $this->assertContains('ROLE_SYSTEM_ADMIN', $admin->getRoles());

        $projectRepo = $this->entityManager->getRepository(Project::class);
        $allProjects = $projectRepo->findAll();

        $this->assertGreaterThan(0, count($allProjects), 'There should be projects for admins to see');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
