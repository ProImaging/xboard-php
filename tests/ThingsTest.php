<?php

declare(strict_types=1);

namespace XBoard\Tests;

use PHPUnit\Framework\TestCase;
use XBoard\Internal\Things;

final class ThingsTest extends TestCase
{
    public function testCopiesNestedMetaBodyRawOntoBodyRawForNotes(): void
    {
        $flat = Things::flattenThingContent([
            '_id' => 'n1',
            'type' => 'NOTE',
            'meta' => ['body' => '<p>Hello</p>', 'bodyRaw' => 'Hello'],
        ]);
        $this->assertSame('Hello', $flat['bodyRaw']);
        $this->assertSame('<p>Hello</p>', $flat['body']);
    }

    public function testStripsHtmlWhenOnlyMetaBodyIsPresent(): void
    {
        $flat = Things::flattenThingContent([
            'type' => 'NOTE',
            'meta' => ['body' => '<h1>Title</h1>'],
        ]);
        $this->assertSame('Title', $flat['bodyRaw']);
    }

    public function testLeavesFileThingsUnchangedBesidesAShallowCopy(): void
    {
        $flat = Things::flattenThingContent([
            '_id' => 'f1',
            'type' => 'FILE',
            'meta' => ['fileName' => 'a.pdf'],
        ]);
        $this->assertSame(['fileName' => 'a.pdf'], $flat['meta']);
        $this->assertArrayNotHasKey('body', $flat);
    }

    public function testDetectsNotesAndFiles(): void
    {
        $this->assertTrue(Things::isNoteThing(['type' => 'NOTE']));
        $this->assertTrue(Things::isFileThing(['thingType' => 'FILE']));
        $this->assertTrue(Things::isFileThing(['type' => 'IMAGE']));
        $this->assertSame('Hi & bye', Things::stripHtml('<p>Hi &amp; bye</p>'));
    }
}
