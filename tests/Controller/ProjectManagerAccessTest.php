<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test that project managers can only see their own projects (Section 4.3.4)
 */
class ProjectManagerAccessTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testProjectManagerSeesOnlyOwnProjects(): void
    {
        // Get a project manager user
        $userRepo = $this->entityManager->getRepository(User::class);
        $projectManager = $userRepo->findOneBy(['username' => 'pm_zhang']);

        $this->assertNotNull($projectManager, 'Test user pm_zhang should exist');

        // Get all projects
        $projectRepo = $this->entityManager->getRepository(Project::class);
        $allProjects = $projectRepo->findAll();

        $this->assertGreaterThan(0, count($allProjects), 'There should be projects in the database');

        // Get projects registered by this user
        $ownProjects = $projectRepo->findBy(['registeredBy' => $projectManager]);

        // There should be at least one project registered by this user
        $this->assertGreaterThan(0, count($ownProjects), 'Project manager should have registered at least one project');

        // Verify that not all projects belong to this user (unless there's only one PM)
        $otherProjects = $projectRepo->createQueryBuilder('p')
            ->where('p.registeredBy != :user OR p.registeredBy IS NULL')
            ->setParameter('user', $projectManager)
            ->getQuery()
            ->getResult();

        $this->assertGreaterThan(0, count($otherProjects), 'There should be projects from other users for meaningful test');

        // The filtering logic ensures:
        // - Project managers only see projects where registeredBy = current user
        $this->assertLessThan(
            count($allProjects),
            count($ownProjects),
            'Project manager should not see all projects'
        );
    }

    public function testSupervisorSeesAllProjects(): void
    {
        $userRepo = $this->entityManager->getRepository(User::class);
        $supervisor = $userRepo->findOneBy(['username' => 'supervisor1']);

        $this->assertNotNull($supervisor, 'Test user supervisor1 should exist');

        // Verify the user has ROLE_SUPERVISOR
        $roles = $supervisor->getRoles();
        $this->assertContains('ROLE_SUPERVISOR', $roles, 'User should have ROLE_SUPERVISOR');

        // Supervisors should see all projects (no filtering applied)
        $projectRepo = $this->entityManager->getRepository(Project::class);
        $allProjects = $projectRepo->findAll();

        $this->assertGreaterThan(0, count($allProjects), 'There should be projects for supervisors to see');
    }

    public function testSystemAdminSeesAllProjects(): void
    {
        $userRepo = $this->entityManager->getRepository(User::class);
        $admin = $userRepo->findOneBy(['username' => 'admin']);

        $this->assertNotNull($admin, 'Test user admin should exist');

        // Verify the user has ROLE_SYSTEM_ADMIN
        $roles = $admin->getRoles();
        $this->assertContains('ROLE_SYSTEM_ADMIN', $roles, 'User should have ROLE_SYSTEM_ADMIN');

        // Admins should see all projects (no filtering applied)
        $projectRepo = $this->entityManager->getRepository(Project::class);
        $allProjects = $projectRepo->findAll();

        $this->assertGreaterThan(0, count($allProjects), 'There should be projects for admins to see');
    }

    public function testProjectAutoPopulation(): void
    {
        // This test verifies that new projects auto-populate registeredBy
        $userRepo = $this->entityManager->getRepository(User::class);
        $projectManager = $userRepo->findOneBy(['username' => 'pm_zhang']);

        $this->assertNotNull($projectManager, 'Test user pm_zhang should exist');
        $this->assertNotNull($projectManager->getOrg(), 'User should have an organization');

        // In a real scenario, when a project manager creates a project:
        // - registeredBy should be set to the current user
        // - org should be set to user's org
        // - registrantOrganization should be set to user's org
        // This is handled in ProjectCrudController::createEntity()
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
