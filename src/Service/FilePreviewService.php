<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\File;

/**
 * Determines how a file can be previewed in the browser.
 */
class FilePreviewService
{
    public const TYPE_PDF = 'pdf';
    public const TYPE_IMAGE = 'image';
    public const TYPE_TEXT = 'text';
    public const TYPE_VIDEO = 'video';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_OFFICE = 'office';
    public const TYPE_UNSUPPORTED = 'unsupported';

    public function getPreviewType(File $file): string
    {
        $mimeType = strtolower($file->getMimeType() ?? '');
        $extension = $this->getExtension($file);

        if ($mimeType === 'application/pdf' || $extension === 'pdf') {
            return self::TYPE_PDF;
        }

        if (str_starts_with($mimeType, 'image/') || \in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'], true)) {
            return self::TYPE_IMAGE;
        }

        if (
            str_starts_with($mimeType, 'text/')
            || \in_array($extension, ['txt', 'csv', 'md', 'json', 'xml', 'html', 'htm', 'log', 'yaml', 'yml'], true)
        ) {
            return self::TYPE_TEXT;
        }

        if (str_starts_with($mimeType, 'video/') || \in_array($extension, ['mp4', 'webm', 'ogg'], true)) {
            return self::TYPE_VIDEO;
        }

        if (str_starts_with($mimeType, 'audio/') || \in_array($extension, ['mp3', 'wav', 'ogg', 'm4a'], true)) {
            return self::TYPE_AUDIO;
        }

        if ($this->isOfficeType($mimeType, $extension)) {
            return self::TYPE_OFFICE;
        }

        return self::TYPE_UNSUPPORTED;
    }

    public function canPreview(File $file): bool
    {
        return $this->getPreviewType($file) !== self::TYPE_UNSUPPORTED;
    }

    private function getExtension(File $file): string
    {
        $name = $file->getOriginalName() ?? $file->getFileName() ?? '';

        return strtolower(pathinfo($name, PATHINFO_EXTENSION));
    }

    private function isOfficeType(string $mimeType, string $extension): bool
    {
        if (\in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'wps', 'et', 'dps'], true)) {
            return true;
        }

        return \in_array($mimeType, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ], true);
    }
}
