<?php

declare(strict_types=1);

namespace App\Tests\Service\Lifecycle;

use App\Entity\File;
use App\Entity\PreliminaryDecision;
use App\Service\Lifecycle\LifecycleStageAttachmentCatalog;
use App\Service\Lifecycle\ProjectLifecycleStageRegistry;
use App\Service\Lifecycle\StageAttachmentComplianceService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class StageAttachmentComplianceServiceTest extends TestCase
{
    private StageAttachmentComplianceService $service;

    private ProjectLifecycleStageRegistry $registry;

    protected function setUp(): void
    {
        $this->service = new StageAttachmentComplianceService();
        $this->registry = new ProjectLifecycleStageRegistry($this->createMock(EntityManagerInterface::class));
    }

    public function testBuildChecklistMarksUploadedCategoriesAsSatisfied(): void
    {
        $definition = $this->registry->find('preliminary');
        $this->assertNotNull($definition);

        $stage = new PreliminaryDecision();
        $stage->addFile((new File())->setCategory('project_proposal'));
        $stage->addFile((new File())->setCategory('feasibility_study'));

        $checklist = $this->service->buildChecklist($definition, $stage);

        $byKey = [];
        foreach ($checklist as $item) {
            $byKey[$item['key']] = $item;
        }

        $this->assertTrue($byKey['project_proposal']['satisfied']);
        $this->assertTrue($byKey['feasibility_study']['satisfied']);
        $this->assertFalse($byKey['funding_arrangement']['satisfied']);
        $this->assertFalse($byKey['scheme_comparison']['required']);
    }

    public function testIsCompliantRequiresAllMandatoryAttachments(): void
    {
        $definition = $this->registry->find('preliminary');
        $this->assertNotNull($definition);

        $stage = new PreliminaryDecision();
        $this->assertFalse($this->service->isCompliant($definition, $stage));

        foreach ($definition->requiredAttachments as $requirement) {
            if (!$requirement->required) {
                continue;
            }
            $stage->addFile((new File())->setCategory($requirement->key));
        }

        $this->assertTrue($this->service->isCompliant($definition, $stage));
        $this->assertSame(0, $this->service->countMissingRequired($definition, $stage));
    }

    public function testNullEntityIsNotCompliantWhenRequirementsExist(): void
    {
        $definition = $this->registry->find('preliminary');
        $this->assertNotNull($definition);

        $this->assertFalse($this->service->isCompliant($definition, null));
        $this->assertGreaterThan(0, $this->service->countMissingRequired($definition, null));
    }
}
