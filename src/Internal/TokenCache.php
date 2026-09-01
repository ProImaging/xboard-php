<?php

declare(strict_types=1);

namespace XBoard\Internal;

/**
 * @phpstan-type CachedToken array{accessToken: string, expiresAtMs: int}
 */
final class TokenCache
{
    /** @var CachedToken|null */
    private ?array $cached = null;

    /**
     * @return CachedToken|null
     */
    public function get(): ?array
    {
        return $this->cached;
    }

    public function set(string $accessToken, int $expiresInSeconds, int $leewaySeconds): void
    {
        $leewayMs = max(0, $leewaySeconds) * 1000;
        $ttlMs = max(0, $expiresInSeconds) * 1000;
        $this->cached = [
            'accessToken' => $accessToken,
            'expiresAtMs' => self::nowMs() + max(0, $ttlMs - $leewayMs),
        ];
    }

    public function clear(): void
    {
        $this->cached = null;
    }

    public function isFresh(?int $now = null): bool
    {
        $now ??= self::nowMs();

        return $this->cached !== null && $this->cached['expiresAtMs'] > $now;
    }

    private static function nowMs(): int
    {
        return (int) floor(microtime(true) * 1000);
    }
}
