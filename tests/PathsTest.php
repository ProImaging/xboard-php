<?php

declare(strict_types=1);

namespace XBoard\Tests;

use PHPUnit\Framework\TestCase;
use XBoard\Internal\Paths;

final class PathsTest extends TestCase
{
    public function testNormalizesTrailingSlashesOnBaseUrl(): void
    {
        $this->assertSame('https://api.example.com', Paths::normalizeBaseUrl('https://api.example.com/'));
    }

    public function testBuildsContentAndAuthUrls(): void
    {
        $this->assertSame(
            'https://api.example.com/content/boards',
            Paths::buildUrl('https://api.example.com', 'content', '/boards'),
        );
        $this->assertSame(
            'https://api.example.com/auth/api-keys/exchange',
            Paths::buildUrl('https://api.example.com/', 'auth', 'api-keys/exchange'),
        );
        $this->assertSame(
            'https://api.example.com/people/account/customers',
            Paths::buildUrl('https://api.example.com', 'people', '/account/customers'),
        );
        $this->assertSame(
            'https://api.example.com/file-proc/board/b/post/p/file',
            Paths::buildUrl('https://api.example.com', 'file-proc', '/board/b/post/p/file'),
        );
    }

    public function testAllowsServicePathOverrides(): void
    {
        $this->assertSame('/v1/content', Paths::resolveServicePath('content', ['content' => '/v1/content']));
        $this->assertSame(
            'https://api.example.com/v1/content/boards',
            Paths::buildUrl('https://api.example.com', 'content', '/boards', ['content' => '/v1/content']),
        );
    }
}
