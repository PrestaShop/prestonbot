<?php

namespace Tests\AppBundle\PullRequests;

use AppBundle\PullRequests\BodyParser;
use PHPUnit\Framework\TestCase;

/**
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 */
class BodyParserTest extends TestCase
{
    private $bodyParser;

    protected function setUp()
    {
        $this->bodyParser = new BodyParser(file_get_contents(__DIR__.'/../../Resources/PullRequestBody/feature.txt'));
    }

    public function testGetBranch()
    {
        $this->assertSame('develop', $this->bodyParser->getBranch());
    }

    public function testGetDescription()
    {
        $this->assertSame('Such a great description', $this->bodyParser->getDescription());
    }

    public function testIsDeprecated()
    {
        $this->assertFalse($this->bodyParser->willDeprecateCode());
    }

    public function testIsBackwardCompatible()
    {
        $this->assertFalse($this->bodyParser->isBackwardCompatible());
    }

    public function testGetTestingScenario()
    {
        $this->assertSame('To test it, launch unit tests', $this->bodyParser->getTestingScenario());
    }

    public function testGetCategory()
    {
        $this->assertSame('BO', $this->bodyParser->getCategory());
    }

    public function testGetType()
    {
        $this->assertSame($this->bodyParser->getType(), 'new feature');
        $this->assertContains($this->bodyParser->getType(), $this->bodyParser->getValidTypes());
        $this->assertTrue($this->bodyParser->isAFeature());
        $this->assertFalse($this->bodyParser->isAnImprovement());
        $this->assertFalse($this->bodyParser->isABugFix());
        $this->assertFalse($this->bodyParser->isASmallFix());
        $this->assertFalse($this->bodyParser->isARefacto());
    }

    public function testGetTestCategory()
    {
        $this->bodyParser = new BodyParser(file_get_contents(__DIR__.'/../../Resources/PullRequestBody/TE_category.txt'));

        $this->assertSame('TE', $this->bodyParser->getCategory());
        $this->assertTrue($this->bodyParser->isTestCategory());
    }

    public function testGetMergeCategory()
    {
        $this->bodyParser = new BodyParser(file_get_contents(__DIR__.'/../../Resources/PullRequestBody/ME_category.txt'));

        $this->assertSame('ME', $this->bodyParser->getCategory());
        $this->assertTrue($this->bodyParser->isMergeCategory());
    }

    public function testGetTypeWithoutSpaces()
    {
        $this->bodyParser = new BodyParser(file_get_contents(__DIR__.'/../../Resources/PullRequestBody/improvement.txt'));

        $this->assertSame($this->bodyParser->getType(), 'improvement');
        $this->assertContains($this->bodyParser->getType(), $this->bodyParser->getValidTypes());
        $this->assertFalse($this->bodyParser->isAFeature());
        $this->assertTrue($this->bodyParser->isAnImprovement());
        $this->assertFalse($this->bodyParser->isABugFix());
        $this->assertFalse($this->bodyParser->isASmallFix());
        $this->assertFalse($this->bodyParser->isARefacto());
    }

    public function testGetTypeForBugFix()
    {
        $this->bodyParser = new BodyParser(file_get_contents(__DIR__.'/../../Resources/PullRequestBody/bug_fix.txt'));

        $this->assertSame($this->bodyParser->getType(), 'bug fix');
        $this->assertContains($this->bodyParser->getType(), $this->bodyParser->getValidTypes());
        $this->assertFalse($this->bodyParser->isAFeature());
        $this->assertFalse($this->bodyParser->isAnImprovement());
        $this->assertTrue($this->bodyParser->isABugFix());
        $this->assertFalse($this->bodyParser->isASmallFix());
        $this->assertFalse($this->bodyParser->isARefacto());
    }

    public function testGetTicket()
    {
        $this->assertSame('#1234', $this->bodyParser->getRelatedTicket());
    }

    public function testGetTicketUrl()
    {
        $this->bodyParser = new BodyParser(file_get_contents(__DIR__.'/../../Resources/PullRequestBody/feature.txt'));
        $this->assertSame('#1234', $this->bodyParser->getRelatedTicket());
    }

    public function testRepeatBodParserTestsWithSpaces()
    {
        $this->bodyParser = new BodyParser(file_get_contents(__DIR__.'/../../Resources/PullRequestBody/with_spaces.txt'));

        $this->testGetBranch();
        $this->testGetDescription();
        $this->testGetType();
        $this->testGetTicket();
        $this->testIsDeprecated();
        $this->testIsBackwardCompatible();
    }

    public function testRepeatBodParserTestsWithSpacesAndDot()
    {
        $this->bodyParser = new BodyParser(file_get_contents(__DIR__.'/../../Resources/PullRequestBody/with_spaces_and_dots.txt'));

        $this->testGetBranch();
        $this->assertSame('Such a great description.', $this->bodyParser->getDescription());
        $this->testGetType();
        $this->testGetTicket();
        $this->testIsDeprecated();
        $this->testIsBackwardCompatible();
    }

    public function testGetEmptyDescription()
    {
        $this->bodyParser = new BodyParser(file_get_contents(__DIR__.'/../../Resources/PullRequestBody/missing_description.txt'));

        $this->assertSame($this->bodyParser->getType(), 'bug fix');
        $this->assertContains($this->bodyParser->getType(), $this->bodyParser->getValidTypes());
        $this->assertFalse($this->bodyParser->isAFeature());
        $this->assertFalse($this->bodyParser->isAnImprovement());
        $this->assertTrue($this->bodyParser->isABugFix());
        $this->assertFalse($this->bodyParser->isASmallFix());
        $this->assertFalse($this->bodyParser->isARefacto());
        $this->assertEmpty($this->bodyParser->getDescription());
    }
}
