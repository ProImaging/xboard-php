<?php

declare(strict_types=1);

namespace XBoard\Internal;

final class Things
{
    /**
     * @return list<mixed>
     */
    public static function collectThings(mixed $payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        $data = $payload['data'] ?? $payload;
        if (!is_array($data)) {
            return [];
        }

        foreach (['info', 'things', 'allThings', 'items'] as $key) {
            $value = $data[$key] ?? null;
            if (is_array($value) && array_is_list($value)) {
                return $value;
            }
        }

        if (array_is_list($data)) {
            return $data;
        }

        return [];
    }

    public static function thingType(mixed $item): string
    {
        if (!is_array($item)) {
            return '';
        }

        $type = $item['type'] ?? $item['thingType'] ?? $item['Type'] ?? '';

        return strtoupper(is_string($type) ? $type : '');
    }

    public static function isNoteThing(mixed $item): bool
    {
        return self::thingType($item) === 'NOTE';
    }

    public static function isFileThing(mixed $item): bool
    {
        $type = self::thingType($item);

        return $type === 'FILE' || $type === 'IMAGE' || $type === 'VIDEO';
    }

    public static function stripHtml(string $value): string
    {
        $stripped = preg_replace('/<[^>]*>/', ' ', $value) ?? $value;
        $stripped = (string) preg_replace('/&nbsp;/i', ' ', $stripped);
        $stripped = (string) preg_replace('/&amp;/i', '&', $stripped);
        $stripped = (string) preg_replace('/&lt;/i', '<', $stripped);
        $stripped = (string) preg_replace('/&gt;/i', '>', $stripped);
        $stripped = (string) preg_replace('/&quot;/i', '"', $stripped);
        $stripped = (string) preg_replace('/\s+/', ' ', $stripped);

        return trim($stripped);
    }

    /**
     * Copy nested `meta.body` / `meta.bodyRaw` onto top-level fields for notes.
     *
     * @return array<string, mixed>
     */
    public static function flattenThingContent(mixed $item): array
    {
        if (!is_array($item) || array_is_list($item)) {
            return [];
        }

        if (!self::isNoteThing($item)) {
            return $item;
        }

        $meta = isset($item['meta']) && is_array($item['meta']) ? $item['meta'] : null;
        $nestedBody = is_array($meta) && is_string($meta['body'] ?? null) ? $meta['body'] : '';
        $nestedRaw = is_array($meta) && is_string($meta['bodyRaw'] ?? null) ? $meta['bodyRaw'] : '';
        $recordBody = is_string($item['body'] ?? null) ? $item['body'] : '';
        $recordRaw = is_string($item['bodyRaw'] ?? null) ? $item['bodyRaw'] : '';

        $body = self::firstNonEmpty($recordBody, $nestedBody);
        $bodyRaw = self::firstNonEmpty(
            $recordRaw,
            $nestedRaw,
            self::stripHtml($recordBody),
            self::stripHtml($nestedBody),
        );

        $flat = $item;
        if ($body !== '') {
            $flat['body'] = $body;
        }
        if ($bodyRaw !== '') {
            $flat['bodyRaw'] = $bodyRaw;
        }

        return $flat;
    }

    private static function firstNonEmpty(string ...$values): string
    {
        foreach ($values as $value) {
            if (trim($value) !== '') {
                return $value;
            }
        }

        return '';
    }
}
