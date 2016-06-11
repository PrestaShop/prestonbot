<?php

namespace tests\AppBundle\PullRequests;

use AppBundle\PullRequests\BodyParser;
use Lpdigital\Github\Parser\WebhookResolver;

/**
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 */
class BodyParserTest extends \PHPUnit_Framework_TestCase
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
        $this->assertSame($this->bodyParser->getBody(), $this->event->pullRequest->getBody());
    }

    public function testGetBranch()
    {
        $this->assertSame($this->bodyParser->getBranch(), 'develop');
    }

    public function testGetDescription()
    {
        $this->assertSame($this->bodyParser->getDescription(), 'Such a great description');
    }

    public function testGetType()
    {
        $this->assertSame($this->bodyParser->getType(), 'new feature');
        $this->assertTrue($this->bodyParser->isAFeature());
        $this->assertFalse($this->bodyParser->isAnImprovement());
        $this->assertFalse($this->bodyParser->isABugFix());
        $this->assertFalse($this->bodyParser->isASmallFix());
        $this->assertFalse($this->bodyParser->isARefacto());
    }

    private function getExpectedBody()
    {
        $webhookResponse = file_get_contents(__DIR__.'/../webhook_examples/pull_request_body.opened.json');
        $data = json_decode($webhookResponse, true);

        return $this->webhookResolver
            ->resolve($data)
            ->pullRequest
            ->getBody();
    }
}
