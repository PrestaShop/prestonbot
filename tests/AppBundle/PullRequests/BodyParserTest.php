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
        $this->assertTrue($this->bodyParser->isBackwardCompatible());
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
        $this->assertFalse($this->bodyParser->isARefacto());
        $this->assertEmpty($this->bodyParser->getDescription());
    }

    /**
     * @dataProvider provideBackwardCompatibleTests
     *
     * @param string $file
     * @param bool   $isBackwardCompatible
     *
     * @return void
     */
    public function testBackwardCompatible(string $file, bool $isBackwardCompatible): void
    {
        $bodyParser = new BodyParser(file_get_contents($file));
        $this->assertSame($isBackwardCompatible, $bodyParser->isBackwardCompatible());
    }

    public function provideBackwardCompatibleTests(): array
    {
        $base = __DIR__.'/../../Resources/PullRequestBody/';

        return [
            'Backward compatible' => [
                $base.'bug_fix.txt',
                true,
            ],
            'Backward incompatible' => [
                $base.'bug_fix_bc_break.txt',
                false,
            ],
            'Backward incompatible with comments' => [
                $base.'bug_fix_bc_break_alt.txt',
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideWillDeprecateCodeTests
     *
     * @param string $file
     * @param bool   $willDeprecateCode
     *
     * @return void
     */
    public function testWillDeprecateCode(string $file, bool $willDeprecateCode): void
    {
        $bodyParser = new BodyParser(file_get_contents($file));
        $this->assertSame($willDeprecateCode, $bodyParser->willDeprecateCode());
    }

    public function provideWillDeprecateCodeTests(): array
    {
        $base = __DIR__.'/../../Resources/PullRequestBody/';

        return [
            'No deprecates' => [
                $base.'bug_fix_no_deprecate.txt',
                true,
            ],
            'Deprecates' => [
                $base.'bug_fix.txt',
                false,
            ],
            'Deprecates with comments' => [
                $base.'bug_fix_deprecate_alt.txt',
                false,
            ],
        ];
    }
}
