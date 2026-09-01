<?php

declare(strict_types=1);

namespace XBoard\Tests;

use PHPUnit\Framework\TestCase;
use XBoard\BoardType;
use XBoard\Internal\BoardKind;

final class BoardTypeTest extends TestCase
{
    public function testPartnerValues(): void
    {
        $this->assertSame('shared', BoardType::Shared->value);
        $this->assertSame('private', BoardType::Private->value);
    }

    public function testNormalizeAcceptsEnumAndString(): void
    {
        $this->assertSame('shared', BoardKind::normalize(BoardType::Shared));
        $this->assertSame('private', BoardKind::normalize('PRIVATE'));
    }

    public function testNormalizeRejectsUnknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('boardType must be shared or private');
        BoardKind::normalize('public');
    }

    public function testRequireExternalCustomerId(): void
    {
        $this->assertSame('CRM-1001', BoardKind::requireExternalCustomerId(' CRM-1001 '));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('externalCustomerId is required');
        BoardKind::requireExternalCustomerId('  ');
    }
}
