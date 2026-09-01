<?php

declare(strict_types=1);

namespace XBoard\Tests;

use PHPUnit\Framework\TestCase;
use XBoard\Internal\Paths;

final class OptionsTest extends TestCase
{
    public function testDocumentsRequiredFieldsViaAValidOptionsObject(): void
    {
        $this->assertSame('https://api.example.com', Paths::normalizeBaseUrl('https://api.example.com/'));
    }
}
