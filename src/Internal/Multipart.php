<?php

declare(strict_types=1);

namespace XBoard\Internal;

use XBoard\FileUpload;

final class Multipart
{
    /**
     * @param string|\SplFileInfo|resource|FileUpload $file
     *
     * @return array{name: string, contents: mixed, filename: string, headers?: array<string, string>}
     */
    public static function filePart(mixed $file, ?string $filenameOverride = null): array
    {
        if ($file instanceof FileUpload) {
            $filename = trim($filenameOverride ?? '') !== '' ? trim((string) $filenameOverride) : $file->filename;
            $part = [
                'name' => 'file',
                'contents' => $file->contents,
                'filename' => $filename !== '' ? $filename : 'upload',
            ];
            if ($file->mimeType !== null && $file->mimeType !== '') {
                $part['headers'] = ['Content-Type' => $file->mimeType];
            }

            return $part;
        }

        if ($file instanceof \SplFileInfo) {
            $path = $file->getPathname();
            $filename = trim($filenameOverride ?? '') !== '' ? trim((string) $filenameOverride) : $file->getFilename();

            return [
                'name' => 'file',
                'contents' => fopen($path, 'r') ?: throw new \InvalidArgumentException('Unable to read file'),
                'filename' => $filename !== '' ? $filename : 'upload',
            ];
        }

        if (is_string($file)) {
            if (!is_file($file)) {
                throw new \InvalidArgumentException('file is required');
            }
            $filename = trim($filenameOverride ?? '') !== '' ? trim((string) $filenameOverride) : basename($file);

            return [
                'name' => 'file',
                'contents' => fopen($file, 'r') ?: throw new \InvalidArgumentException('Unable to read file'),
                'filename' => $filename !== '' ? $filename : 'upload',
            ];
        }

        if (is_resource($file)) {
            $filename = trim($filenameOverride ?? '') !== '' ? trim((string) $filenameOverride) : 'upload';

            return [
                'name' => 'file',
                'contents' => $file,
                'filename' => $filename,
            ];
        }

        throw new \InvalidArgumentException('file is required');
    }
}
