<?php

declare(strict_types=1);

namespace XBoard\Internal;

final class Paths
{
    /** @var array<string, string> */
    private const DEFAULT_SERVICE_PATHS = [
        'auth' => '/auth',
        'content' => '/content',
        'people' => '/people',
        'file-proc' => '/file-proc',
    ];

    public static function normalizeBaseUrl(string $baseUrl): string
    {
        $trimmed = trim($baseUrl);
        if ($trimmed === '') {
            throw new \InvalidArgumentException('baseUrl must be a non-empty string');
        }

        return rtrim($trimmed, '/');
    }

    /**
     * @param array<string, string>|null $overrides
     */
    public static function resolveServicePath(string $service, ?array $overrides = null): string
    {
        $raw = $overrides[$service] ?? self::DEFAULT_SERVICE_PATHS[$service] ?? '';
        $withSlash = str_starts_with($raw, '/') ? $raw : '/' . $raw;
        $normalized = rtrim($withSlash, '/');

        return $normalized !== '' ? $normalized : '/';
    }

    /**
     * Join gateway base URL + service prefix + route path.
     * Example: `https://api.example.com` + content + `/boards`
     * → `https://api.example.com/content/boards`
     *
     * @param array<string, string>|null $servicePaths
     */
    public static function buildUrl(
        string $baseUrl,
        string $service,
        string $route,
        ?array $servicePaths = null,
    ): string {
        $base = self::normalizeBaseUrl($baseUrl);
        $prefix = self::resolveServicePath($service, $servicePaths);
        $path = str_starts_with($route, '/') ? $route : '/' . $route;

        return $base . $prefix . $path;
    }
}
