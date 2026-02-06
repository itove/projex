<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ProjectRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ProjectNumberGenerator
{
    public function __construct(
        private readonly ProjectRepository $repository,
        #[Autowire(param: 'project_number.prefix')]
        private readonly string $prefix,
        #[Autowire(param: 'project_number.year_format')]
        private readonly string $yearFormat,
    ) {
    }

    public function generate(): string
    {
        $year = (int) date($this->yearFormat);

        return $this->repository->generateProjectNumber($this->prefix, $year);
    }
}
