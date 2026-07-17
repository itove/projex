<?php

declare(strict_types=1);

namespace App\Exception;

use App\Entity\Project;

/**
 * Thrown when creating a progress report for a project that has no
 * week/month reporting cadence configured. Controllers catch this to
 * show a flash message instead of a 400 error page.
 */
final class MissingProgressReportIntervalException extends \RuntimeException
{
    public function __construct(
        private readonly ?Project $project = null,
        string $message = '该项目未设置进度填报周期，请先在项目信息中配置「进度填报周期」（每周/每月）。',
    ) {
        parent::__construct($message);
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }
}
