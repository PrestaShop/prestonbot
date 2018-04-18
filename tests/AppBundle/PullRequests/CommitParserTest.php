<?php

namespace tests\AppBundle\PullRequests;

use AppBundle\PullRequests\CommitParser;
use Lpdigital\Github\Entity\PullRequest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 */
class CommitParserTest extends WebTestCase
{
    public static $kernel;
    public static $pullRequest;

    public function setUp()
    {
        $kernel = self::getKernelClass();

        self::$kernel = new $kernel('dev', true);
        self::$kernel->boot();

        self::$pullRequest = $this->createMock(PullRequest::class);
    }

    /**
     * dump test for coverage.
     */
    public function testGetMessage()
    {
        $parser = new CommitParser('foo', self::$pullRequest);
        $this->assertSame($parser->getMessage(), 'foo');
    }

    /**
     * @dataProvider getCommits
     *
     * @param mixed $label
     * @param mixed $expected
     */
    public function testValidation($label, $expected)
    {
        $validator = self::$kernel->getContainer()->get('validator');
        $parser = new CommitParser($label, self::$pullRequest);

        $validationsErrors = $validator->validate($parser);
        $isValid = (0 === count($validationsErrors));

        $this->assertTrue($isValid === $expected);
    }

    public static function getCommits()
    {
        return [
            ['bo: fixed stuff', false],
            ['fixed stuff', false],
            ['BO: fixed stuff', true],
            ['BA: fixed stuff', false],
            ['FO : fixed stuff', false],
            ['IN: installation process ok', true],
        ];
    }
}
