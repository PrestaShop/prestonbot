<?php

namespace Tests\AppBundle\PullRequests;

use AppBundle\PullRequests\BodyParser;
use Lpdigital\Github\Parser\WebhookResolver;
use PHPUnit\Framework\TestCase;

/**
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 */
class BodyParserTest extends TestCase
{
    private $bodyParser;
    private $event;
    private $webhookResolver;

    protected function setUp()
    {
        $this->webhookResolver = new WebhookResolver();
        $webhookResponse = file_get_contents(__DIR__.'/../webhook_examples/pull_request_body.opened.json');
        $data = json_decode($webhookResponse, true);
        $this->event = $this->webhookResolver->resolve($data);
        $this->bodyParser = new BodyParser($this->event->pullRequest->getBody());
    }

    public function testGetBody()
    {
        $this->assertSame($this->event->pullRequest->getBody(), $this->bodyParser->getBody());
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

    public function testGetTypeWithoutSpaces()
    {
        $this->webhookResolver = new WebhookResolver();
        $webhookResponse = file_get_contents(__DIR__.'/../webhook_examples/pull_request_body.opened.improvement.json');
        $data = json_decode($webhookResponse, true);
        $this->event = $this->webhookResolver->resolve($data);
        $this->bodyParser = new BodyParser($this->event->pullRequest->getBody());

        $this->assertSame($this->bodyParser->getType(), 'improvement');
        $this->assertContains($this->bodyParser->getType(), $this->bodyParser->getValidTypes());
        $this->assertFalse($this->bodyParser->isAFeature());
        $this->assertTrue($this->bodyParser->isAnImprovement());
        $this->assertFalse($this->bodyParser->isABugFix());
        $this->assertFalse($this->bodyParser->isASmallFix());
        $this->assertFalse($this->bodyParser->isARefacto());
    }

    public function testGetTicket()
    {
        $this->assertSame('http://forge.prestashop.com/browse/TEST-1234', $this->bodyParser->getRelatedTicket());
    }

    public function testRepeatBodParserTestsWithSpaces()
    {
        $this->webhookResolver = new WebhookResolver();
        $webhookResponse = file_get_contents(__DIR__.'/../webhook_examples/pull_request_body.spaces.opened.json');
        $data = json_decode($webhookResponse, true);
        $this->event = $this->webhookResolver->resolve($data);
        $this->bodyParser = new BodyParser($this->event->pullRequest->getBody());

        $this->testGetBody();
        $this->testGetBranch();
        $this->testGetDescription();
        $this->testGetType();
        $this->testGetTicket();
        $this->testIsDeprecated();
        $this->testIsBackwardCompatible();
    }
}
