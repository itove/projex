<?php

declare(strict_types=1);

namespace App\Exception;

use App\Entity\Project;

/**
 * Thrown when creating a lifecycle stage whose previous stage is still missing
 * required attachments.
 */
final class PreviousStageAttachmentsIncompleteException extends \RuntimeException
{
    public function __construct(
        private readonly ?Project $project = null,
        string $message = '请先上传上一阶段的全部必传文件，才能开始登记本阶段。',
    ) {
        parent::__construct($message);
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }
}
