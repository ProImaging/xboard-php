<?php

declare(strict_types=1);

namespace XBoard\Tests;

use PHPUnit\Framework\TestCase;
use XBoard\Errors\AuthenticationError;
use XBoard\Errors\NotFoundError;
use XBoard\Errors\PermissionError;
use XBoard\Errors\RateLimitError;
use XBoard\Errors\XBoardError;

final class ErrorsTest extends TestCase
{
    public function testMapsStatusCodesToTypedErrors(): void
    {
        $this->assertInstanceOf(AuthenticationError::class, XBoardError::fromStatus(401, 'nope'));
        $this->assertInstanceOf(PermissionError::class, XBoardError::fromStatus(403, 'nope'));
        $this->assertInstanceOf(NotFoundError::class, XBoardError::fromStatus(404, 'nope'));
        $this->assertInstanceOf(RateLimitError::class, XBoardError::fromStatus(429, 'nope'));
    }
}
