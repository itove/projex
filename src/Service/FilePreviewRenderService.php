<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\File;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Html as SpreadsheetHtmlWriter;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Symfony\Component\Mime\MimeTypes;

class FilePreviewRenderService
{
    public function __construct(
        private readonly FilePreviewService $filePreviewService,
    ) {
    }

    public function canRenderHtml(File $file): bool
    {
        return $this->filePreviewService->getPreviewType($file) === FilePreviewService::TYPE_OFFICE
            && \in_array($this->getExtension($file), ['doc', 'docx', 'xls', 'xlsx'], true);
    }

    public function renderHtml(File $file, string $filePath): string
    {
        return match ($this->getExtension($file)) {
            'xls', 'xlsx' => $this->renderSpreadsheet($filePath),
            'doc', 'docx' => $this->renderWord($filePath),
            default => throw new \InvalidArgumentException('Unsupported office format for HTML preview.'),
        };
    }

    public function resolveMimeType(File $file, string $filePath): string
    {
        if ($file->getMimeType()) {
            return $file->getMimeType();
        }

        $guessed = MimeTypes::getDefault()->guessMimeType($filePath);
        if ($guessed !== null) {
            return $guessed;
        }

        return match ($this->getExtension($file)) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'txt' => 'text/plain; charset=UTF-8',
            'html', 'htm' => 'text/html; charset=UTF-8',
            'csv' => 'text/csv; charset=UTF-8',
            'json' => 'application/json; charset=UTF-8',
            'xml' => 'application/xml; charset=UTF-8',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mp3' => 'audio/mpeg',
            default => 'application/octet-stream',
        };
    }

    private function renderSpreadsheet(string $filePath): string
    {
        $spreadsheet = SpreadsheetIOFactory::load($filePath);
        $writer = new SpreadsheetHtmlWriter($spreadsheet);
        $writer->setEmbedImages(true);

        ob_start();
        $writer->save('php://output');
        $tableHtml = ob_get_clean() ?: '';

        return $this->wrapPreviewDocument($tableHtml, 'spreadsheet-preview');
    }

    private function renderWord(string $filePath): string
    {
        $phpWord = WordIOFactory::load($filePath);
        $writer = WordIOFactory::createWriter($phpWord, 'HTML');

        ob_start();
        $writer->save('php://output');
        $documentHtml = ob_get_clean() ?: '';

        return $this->wrapPreviewDocument($documentHtml, 'word-preview');
    }

    private function wrapPreviewDocument(string $bodyHtml, string $wrapperClass): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文档预览</title>
    <style>
        body {
            margin: 0;
            padding: 1.25rem;
            background: #fff;
            color: #212529;
            font-family: "Microsoft YaHei", "PingFang SC", sans-serif;
            line-height: 1.6;
        }
        .{$wrapperClass} {
            max-width: 100%;
            overflow-x: auto;
        }
        .{$wrapperClass} table {
            border-collapse: collapse;
            width: 100%;
        }
        .{$wrapperClass} table td,
        .{$wrapperClass} table th {
            border: 1px solid #dee2e6;
            padding: 0.35rem 0.5rem;
            vertical-align: top;
        }
        .{$wrapperClass} img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="{$wrapperClass}">{$bodyHtml}</div>
</body>
</html>
HTML;
    }

    private function getExtension(File $file): string
    {
        $name = $file->getOriginalName() ?? $file->getFileName() ?? '';

        return strtolower(pathinfo($name, PATHINFO_EXTENSION));
    }
}
