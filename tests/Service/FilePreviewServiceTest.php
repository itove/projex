<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\File;
use App\Service\FilePreviewService;
use PHPUnit\Framework\TestCase;

final class FilePreviewServiceTest extends TestCase
{
    private FilePreviewService $service;

    protected function setUp(): void
    {
        $this->service = new FilePreviewService();
    }

    public function testDetectsPdfPreview(): void
    {
        $file = $this->createFile('report.pdf', 'application/pdf');

        self::assertSame(FilePreviewService::TYPE_PDF, $this->service->getPreviewType($file));
    }

    public function testDetectsImagePreview(): void
    {
        $file = $this->createFile('photo.png', 'image/png');

        self::assertSame(FilePreviewService::TYPE_IMAGE, $this->service->getPreviewType($file));
    }

    public function testDetectsOfficePreview(): void
    {
        $file = $this->createFile(
            'plan.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        );

        self::assertSame(FilePreviewService::TYPE_OFFICE, $this->service->getPreviewType($file));
    }

    public function testUnsupportedArchive(): void
    {
        $file = $this->createFile('archive.zip', 'application/zip');

        self::assertSame(FilePreviewService::TYPE_UNSUPPORTED, $this->service->getPreviewType($file));
        self::assertFalse($this->service->canPreview($file));
    }

    private function createFile(string $originalName, ?string $mimeType): File
    {
        $file = new File();
        $file->setOriginalName($originalName);
        $file->setMimeType($mimeType);

        return $file;
    }
}
