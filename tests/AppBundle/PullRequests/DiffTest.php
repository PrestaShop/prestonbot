<?php

namespace tests\AppBundle\PullRequests;

use AppBundle\PullRequests\Diff;

/**
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 */
class DiffTest extends \PHPUnit_Framework_TestCase
{
    private $gitDiff;
    const TRANS_PATTERN = '#(trans\(|l\()#';

    public function testMatch()
    {
        $matched = Diff::match(self::TRANS_PATTERN, $this->getExpectedDiff(true));
        $this->assertTrue($matched);

        $unMatched = Diff::match(self::TRANS_PATTERN, $this->getExpectedDiff(false));
        $this->assertFalse($unMatched);
    }

    private function getExpectedDiff($matched = true)
    {
        $filename = $matched ? 'matched' : 'not_matched';

        return file_get_contents(__DIR__.'/../webhook_examples/git_diff_'.$filename.'.diff');
    }
}
