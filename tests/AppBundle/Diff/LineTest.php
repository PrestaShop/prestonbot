<?php

namespace tests\AppBundle\Diff;

use AppBundle\Diff\Line;

/**
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 */
class LineTest extends \PHPUnit_Framework_TestCase
{
    const TRANS_PATTERN = '#(trans\(|->l\()#';

    /**
     * @dataProvider testCases
     */
    public function testMatch($content, $expected)
    {
        $line = new Line($content);
        $this->assertSame($expected, $line->match(self::TRANS_PATTERN));
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
}
