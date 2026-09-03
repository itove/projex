<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\File;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * File Viewer assets for PDF / Word / Excel preview.
 *
 * @see https://github.com/flyfish-dev/file-viewer
 */
class FileViewerService
{
    private const SUPPORTED_EXTENSIONS = [
        'pdf',
        'doc', 'docx', 'dot', 'rtf', 'odt',
        'xls', 'xlsx', 'xlsm', 'xlsb', 'csv', 'ods', 'fods',
    ];

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        private readonly FilePreviewService $filePreviewService,
    ) {
    }

    public function supports(File $file): bool
    {
        return \in_array($this->filePreviewService->getExtension($file), self::SUPPORTED_EXTENSIONS, true);
    }

    public function isBuilt(): bool
    {
        return $this->getEntrypointScriptPath() !== null;
    }

    public function getEntrypointScriptPath(): ?string
    {
        $manifest = $this->readManifest();
        $entry = $manifest['frontend/file-viewer/main.js'] ?? null;
        if (!\is_array($entry) || !isset($entry['file']) || !\is_string($entry['file'])) {
            return null;
        }

        return '/build/file-viewer/'.$entry['file'];
    }

    /**
     * @return list<string>
     */
    public function getEntrypointCssPaths(): array
    {
        $manifest = $this->readManifest();
        $entry = $manifest['frontend/file-viewer/main.js'] ?? null;
        if (!\is_array($entry) || !isset($entry['css']) || !\is_array($entry['css'])) {
            return [];
        }

        $paths = [];
        foreach ($entry['css'] as $css) {
            if (\is_string($css) && $css !== '') {
                $paths[] = '/build/file-viewer/'.$css;
            }
        }

        return $paths;
    }

    /**
     * @return array<string, mixed>
     */
    private function readManifest(): array
    {
        foreach ([
            $this->projectDir.'/public/build/file-viewer/.vite/manifest.json',
            $this->projectDir.'/public/build/file-viewer/manifest.json',
        ] as $path) {
            if (!is_file($path)) {
                continue;
            }
            try {
                $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                continue;
            }
            if (\is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
}
