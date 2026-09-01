<?php

declare(strict_types=1);

namespace XBoard;

/**
 * In-memory file payload for `$post->addFile` / composer `addFile()` (PHP equivalent of a Blob).
 */
final class FileUpload
{
    public function __construct(
        public readonly string $contents,
        public readonly string $filename,
        public readonly ?string $mimeType = null,
    ) {
    }
}
