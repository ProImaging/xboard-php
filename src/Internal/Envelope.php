<?php

declare(strict_types=1);

namespace XBoard\Internal;

final class Envelope
{
    /**
     * @return array<string, mixed>
     */
    public static function data(mixed $payload): array
    {
        if (!is_array($payload)) {
            return [];
        }
        $data = $payload['data'] ?? null;
        if (is_array($data) && !array_is_list($data)) {
            return $data;
        }

        return $payload;
    }

    public static function postId(mixed $payload): string
    {
        $data = self::data($payload);
        foreach (['postID', 'postId', '_id'] as $key) {
            $value = $data[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        throw new \RuntimeException('Response did not include postID');
    }

    /**
     * @return list<string>
     */
    public static function postIdsFromList(mixed $payload): array
    {
        $ids = [];
        foreach (Things::collectThings($payload) as $item) {
            if (!is_array($item)) {
                continue;
            }
            foreach (['postID', 'postId', '_id', 'id'] as $key) {
                $value = $item[$key] ?? null;
                if (is_string($value) && trim($value) !== '') {
                    $ids[] = $value;
                    break;
                }
            }
        }

        return $ids;
    }
}
