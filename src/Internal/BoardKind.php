<?php

declare(strict_types=1);

namespace XBoard\Internal;

use XBoard\BoardType;

final class BoardKind
{
    public static function normalize(BoardType|string $boardType): string
    {
        $value = $boardType instanceof BoardType
            ? $boardType->value
            : strtolower(trim($boardType));
        if ($value !== BoardType::Shared->value && $value !== BoardType::Private->value) {
            throw new \InvalidArgumentException('boardType must be shared or private');
        }

        return $value;
    }

    public static function requireExternalCustomerId(string $externalCustomerId): string
    {
        $value = trim($externalCustomerId);
        if ($value === '') {
            throw new \InvalidArgumentException('externalCustomerId is required');
        }

        return $value;
    }
}
