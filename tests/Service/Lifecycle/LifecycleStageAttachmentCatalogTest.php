<?php

declare(strict_types=1);

namespace App\Tests\Service\Lifecycle;

use App\Service\Lifecycle\LifecycleStageAttachmentCatalog;
use PHPUnit\Framework\TestCase;

class LifecycleStageAttachmentCatalogTest extends TestCase
{
    public function testEachStageHasAttachmentRequirements(): void
    {
        foreach ([
            'preliminary',
            'approval',
            'planning',
            'preparation',
            'implementation',
            'acceptance',
            'settlement',
        ] as $stageKey) {
            $requirements = LifecycleStageAttachmentCatalog::forStage($stageKey);
            $this->assertNotEmpty($requirements, sprintf('Stage %s should define attachments', $stageKey));
        }
    }

    public function testPreliminaryStageIncludesExpectedKeys(): void
    {
        $keys = array_map(
            static fn ($requirement) => $requirement->key,
            LifecycleStageAttachmentCatalog::forStage('preliminary')
        );

        $this->assertContains('project_proposal', $keys);
        $this->assertContains('feasibility_study', $keys);
        $this->assertContains('preliminary_approval', $keys);
    }

    public function testChoiceMapUsesHumanReadableLabels(): void
    {
        $choices = LifecycleStageAttachmentCatalog::choiceMapForStage('preliminary');

        $this->assertSame('project_proposal', $choices['项目建议书']);
        $this->assertSame('feasibility_study', $choices['可行性研究报告']);
    }

    public function testLabelForKeyResolvesAcrossStages(): void
    {
        $this->assertSame('项目建议书', LifecycleStageAttachmentCatalog::labelForKey('project_proposal'));
        $this->assertSame('竣工结算书', LifecycleStageAttachmentCatalog::labelForKey('settlement_statement'));
        $this->assertNull(LifecycleStageAttachmentCatalog::labelForKey('does_not_exist'));
    }

    public function testAllChoiceMapUsesUniqueLabels(): void
    {
        $choices = LifecycleStageAttachmentCatalog::allChoiceMap();

        $this->assertCount(count($choices), array_unique(array_keys($choices)));
        $this->assertContains('project_proposal', array_values($choices));
    }
}
