<?php

declare(strict_types=1);

namespace App\Service\Lifecycle;

/**
 * One required or optional attachment slot defined for a lifecycle stage.
 * The key is stored on File.category when a user uploads a document.
 */
final readonly class LifecycleStageAttachmentRequirement
{
    /**
     * @param list<string> $allowedExtensions lowercase extensions without dot
     */
    public function __construct(
        public string $key,
        public string $label,
        public bool $required = true,
        public array $allowedExtensions = ['pdf', 'doc', 'docx'],
        public ?int $maxSizeMb = 50,
    ) {
    }
}
