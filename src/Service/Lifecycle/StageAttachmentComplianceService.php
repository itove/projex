<?php

declare(strict_types=1);

namespace App\Service\Lifecycle;

use App\Entity\LifecycleStageInterface;

/**
 * Compares uploaded File.category values against the registry's required
 * attachment list for a lifecycle stage.
 */
final class StageAttachmentComplianceService
{
    /**
     * @return list<array{
     *     key: string,
     *     label: string,
     *     required: bool,
     *     satisfied: bool,
     *     allowedExtensions: list<string>,
     *     maxSizeMb: ?int,
     * }>
     */
    public function buildChecklist(LifecycleStageDefinition $definition, ?LifecycleStageInterface $entity): array
    {
        $uploadedCategories = $this->collectUploadedCategories($entity);
        $items = [];

        foreach ($definition->requiredAttachments as $requirement) {
            $items[] = [
                'key' => $requirement->key,
                'label' => $requirement->label,
                'required' => $requirement->required,
                'satisfied' => \in_array($requirement->key, $uploadedCategories, true),
                'allowedExtensions' => $requirement->allowedExtensions,
                'maxSizeMb' => $requirement->maxSizeMb,
            ];
        }

        return $items;
    }

    public function isCompliant(LifecycleStageDefinition $definition, ?LifecycleStageInterface $entity): bool
    {
        foreach ($this->buildChecklist($definition, $entity) as $item) {
            if ($item['required'] && !$item['satisfied']) {
                return false;
            }
        }

        return true;
    }

    public function countMissingRequired(LifecycleStageDefinition $definition, ?LifecycleStageInterface $entity): int
    {
        $missing = 0;
        foreach ($this->buildChecklist($definition, $entity) as $item) {
            if ($item['required'] && !$item['satisfied']) {
                ++$missing;
            }
        }

        return $missing;
    }

    /**
     * @return list<string>
     */
    private function collectUploadedCategories(?LifecycleStageInterface $entity): array
    {
        if ($entity === null) {
            return [];
        }

        $categories = [];
        foreach ($entity->getFiles() as $file) {
            $category = $file->getCategory();
            if ($category !== null && $category !== '') {
                $categories[] = $category;
            }
        }

        return array_values(array_unique($categories));
    }
}
