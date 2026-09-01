<?php

declare(strict_types=1);

namespace XBoard\Tests;

use PHPUnit\Framework\TestCase;
use XBoard\Internal\TokenCache;

final class TokenCacheTest extends TestCase
{
    public function testIsFreshUntilLeewayAdjustedExpiry(): void
    {
        $cache = new TokenCache();
        $cache->set('tok', 60, 30);
        $this->assertTrue($cache->isFresh());
        $this->assertSame('tok', $cache->get()['accessToken'] ?? null);
    }

    public function testIsNotFreshWhenTtlIsFullyConsumedByLeeway(): void
    {
        $cache = new TokenCache();
        $cache->set('tok', 10, 30);
        $this->assertFalse($cache->isFresh());
    }
}
