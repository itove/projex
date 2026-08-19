<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\File;
use App\Entity\Image;
use App\Entity\Org;
use App\Entity\PreliminaryDecision;
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

    public function testChildOrgUserCannotViewAttachmentFromParentOrgProject(): void
    {
        $parentOrg = $this->orgRepository->findOneBy(['orgCode' => 'ORG-SZGC-001']);
        $childOrg = $this->orgRepository->findOneBy(['orgCode' => 'ORG-ZHCS-002']);
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'pm_li']);
        $parentProject = $this->entityManager->getRepository(Project::class)->findOneBy(['org' => $parentOrg]);
        $childProject = $this->entityManager->getRepository(Project::class)->findOneBy(['org' => $childOrg]);

        $this->assertNotNull($user);
        $this->assertNotNull($parentProject);
        $this->assertNotNull($childProject);

        $this->loginAs($user);

        $parentStage = (new PreliminaryDecision())->setProject($parentProject);
        $parentFile = (new File())->setPreliminaryDecision($parentStage);
        $parentImage = (new Image())->setProject($parentProject);

        $childStage = (new PreliminaryDecision())->setProject($childProject);
        $childFile = (new File())->setPreliminaryDecision($childStage);
        $childImage = (new Image())->setProject($childProject);

        $this->assertFalse($this->orgAccessService->canViewLifecycleAttachment($user, $parentFile));
        $this->assertFalse($this->orgAccessService->canViewLifecycleAttachment($user, $parentImage));
        $this->assertTrue($this->orgAccessService->canViewLifecycleAttachment($user, $childFile));
        $this->assertTrue($this->orgAccessService->canViewLifecycleAttachment($user, $childImage));
    }

    public function testLifecycleAttachmentQueryBuilderIsOrgScoped(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'pm_li']);
        $this->assertNotNull($user);

        $this->loginAs($user);

        $accessibleOrgIds = $this->orgAccessService->getAccessibleOrgIds($user);
        $this->assertNotNull($accessibleOrgIds);
        $this->assertNotEmpty($accessibleOrgIds);

        $inScopeStage = $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(PreliminaryDecision::class, 's')
            ->join('s.project', 'p')
            ->andWhere('p.org IN (:orgIds)')
            ->setParameter('orgIds', $accessibleOrgIds)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $outOfScopeStage = $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(PreliminaryDecision::class, 's')
            ->join('s.project', 'p')
            ->andWhere('p.org NOT IN (:orgIds)')
            ->setParameter('orgIds', $accessibleOrgIds)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $this->assertNotNull($inScopeStage, 'Fixture should include a preliminary decision inside pm_li org tree');
        $this->assertNotNull($outOfScopeStage, 'Fixture should include a preliminary decision outside pm_li org tree');

        $inScopeProject = $inScopeStage->getProject();
        $outOfScopeProject = $outOfScopeStage->getProject();
        $this->assertNotNull($inScopeProject);
        $this->assertNotNull($outOfScopeProject);

        $this->entityManager->beginTransaction();

        try {
            $inScopeFile = (new File())
                ->setFileName('in-scope-file.pdf')
                ->setOriginalName('in-scope-file.pdf')
                ->setPreliminaryDecision($inScopeStage);
            $outOfScopeFile = (new File())
                ->setFileName('out-of-scope-file.pdf')
                ->setOriginalName('out-of-scope-file.pdf')
                ->setPreliminaryDecision($outOfScopeStage);
            $inScopeImage = (new Image())
                ->setFileName('in-scope-image.jpg')
                ->setOriginalName('in-scope-image.jpg')
                ->setProject($inScopeProject);
            $outOfScopeImage = (new Image())
                ->setFileName('out-of-scope-image.jpg')
                ->setOriginalName('out-of-scope-image.jpg')
                ->setProject($outOfScopeProject);

            $this->entityManager->persist($inScopeFile);
            $this->entityManager->persist($outOfScopeFile);
            $this->entityManager->persist($inScopeImage);
            $this->entityManager->persist($outOfScopeImage);
            $this->entityManager->flush();

            $fileQb = $this->entityManager->getRepository(File::class)->createQueryBuilder('entity')
                ->andWhere('entity.id IN (:ids)')
                ->setParameter('ids', [$inScopeFile->getId(), $outOfScopeFile->getId()]);
            $this->orgAccessService->applyLifecycleAttachmentOrgScope($fileQb, $user, 'entity', false);
            $scopedFiles = $fileQb->getQuery()->getResult();

            $imageQb = $this->entityManager->getRepository(Image::class)->createQueryBuilder('entity')
                ->andWhere('entity.id IN (:ids)')
                ->setParameter('ids', [$inScopeImage->getId(), $outOfScopeImage->getId()]);
            $this->orgAccessService->applyLifecycleAttachmentOrgScope($imageQb, $user, 'entity', true);
            $scopedImages = $imageQb->getQuery()->getResult();

            $this->assertCount(1, $scopedFiles);
            $this->assertSame($inScopeFile->getId(), $scopedFiles[0]->getId());
            $this->assertCount(1, $scopedImages);
            $this->assertSame($inScopeImage->getId(), $scopedImages[0]->getId());
        } finally {
            $this->entityManager->rollback();
        }
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
