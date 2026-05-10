<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ProjectSpreadsheetImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:project-import:dump-sample',
    description: 'Generate sample project import XLSX under data/import/ for manual testing',
)]
final class DumpProjectImportSampleCommand extends Command
{
    public function __construct(
        private readonly ProjectSpreadsheetImportService $importService,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dir = $this->projectDir.'/data/import';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            $io->error(sprintf('Cannot create directory: %s', $dir));

            return Command::FAILURE;
        }

        $path = $dir.'/project_import_sample.xlsx';
        $this->importService->saveTemplateTo($path);

        $io->success(sprintf('Sample workbook written to %s', $path));

        return Command::SUCCESS;
    }
}
