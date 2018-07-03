<?php

namespace tests\AppBundle\Diff;

use AppBundle\Diff\Line;
use PHPUnit\Framework\TestCase;

/**
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 */
class LineTest extends TestCase
{
    const TRANS_PATTERN = '#(trans\(|->l\()#';

    /**
     * @dataProvider testCases
     *
     * @param mixed $content
     * @param mixed $expected
     */
    public function testMatch($content, $expected)
    {
        $line = new Line($content);
        $this->assertSame($expected, $line->match(self::TRANS_PATTERN));
    }

    /**
     * @dataProvider filenamesCases
     *
     * @param mixed $content
     * @param mixed $expected
     */
    public function testFilename($content, $expected)
    {
        $line = new Line($content);
        $this->assertSame($expected, $line->getFilename());
    }

    /**
     * @dataProvider filepathsCases
     *
     * @param mixed $content
     * @param mixed $expected
     */
    public function testFilepath($content, $expected)
    {
        $line = new Line($content);
        $this->assertSame($expected, $line->getFilepath());
    }

    public function testCases()
    {
        return [
            ['value.call()', false],
            ["{{ 'foo'|trans() }}", true],
            ['this->trans(', true],
            ['this->translator->trans(', true],
            ['this->translator', false],
            ["object->l['foo']", false],
        ];
    }

    public function filenamesCases()
    {
        return [
            ['diff --git a/b/c/d/e b/b/c/d/e', 'e'],
            ['diff --git a/b b/b', 'b'],
            ['Not a filename', null],
        ];
    }

    public function filepathsCases()
    {
        return [
            ['diff --git a/b/c/d/e b/b/c/d/e', 'b/c/d/e'],
            ['diff --git a/b b/b', 'b'],
            ['Not a filepath', null],
        ];
    }
}
