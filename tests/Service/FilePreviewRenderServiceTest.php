<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\File;
use App\Service\FilePreviewRenderService;
use App\Service\FilePreviewService;
use PHPUnit\Framework\TestCase;

final class FilePreviewRenderServiceTest extends TestCase
{
    private FilePreviewRenderService $service;

    protected function setUp(): void
    {
        $this->service = new FilePreviewRenderService(new FilePreviewService());
    }

    public function testCanRenderDocxAndXlsx(): void
    {
        $docx = $this->createFile('report.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $xlsx = $this->createFile('sheet.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $pptx = $this->createFile('slides.pptx', 'application/vnd.openxmlformats-officedocument.presentationml.presentation');

        self::assertTrue($this->service->canRenderHtml($docx));
        self::assertTrue($this->service->canRenderHtml($xlsx));
        self::assertFalse($this->service->canRenderHtml($pptx));
    }

    public function testResolveMimeTypeFromExtension(): void
    {
        $file = $this->createFile('demo.pdf', null);
        $tmp = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($tmp, '%PDF-1.4 dummy');

        try {
            self::assertSame('application/pdf', $this->service->resolveMimeType($file, $tmp));
        } finally {
            unlink($tmp);
        }
    }

    private function createFile(string $originalName, ?string $mimeType): File
    {
        $file = new File();
        $file->setOriginalName($originalName);
        $file->setMimeType($mimeType);

        return $file;
    }
}
