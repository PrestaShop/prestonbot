<?php

namespace Tests\AppBundle\Event;

use AppBundle\Event\GitHubEvent;
use AppBundle\Event\GitHubEventResolver;
use PHPUnit\Framework\TestCase;
use PrestaShop\Github\WebhookHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class GitHubEventResolverTest extends TestCase
{
    const REPOSITORY_OWNER = 'loveOSS';
    const REPOSITORY_NAME = 'test';
    /**
     * @var GitHubEventResolver
     */
    private $argumentResolver;

    /**
     * @var WebhookHandler
     */
    private $webhookHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->webhookHandler = new WebhookHandler();

        $this->argumentResolver = new GitHubEventResolver(
            $this->webhookHandler,
            $this->logger,
            self::REPOSITORY_OWNER,
            self::REPOSITORY_NAME
        );
    }

    public function testSupportsNoGithubEvent()
    {
        $metadata = new ArgumentMetadata('foo', null, false, false, null);

        $this->assertFalse($this->argumentResolver->supports(Request::create('/'), $metadata));
    }

    public function testSupportsGithubEvent()
    {
        $metadata = new ArgumentMetadata('foo', GitHubEvent::class, false, false, null);

        $this->assertTrue($this->argumentResolver->supports(Request::create('/'), $metadata));
    }

    public function testResolveWithGitHubEvent()
    {
        $metadata = new ArgumentMetadata('foo', GitHubEvent::class, false, false, null);
        $this->logger->expects($this->any())->method('error')->willReturn(null);

        $request = Request::create(
            '/',
            'POST',
            [],
            [],
            [],
            [],
            file_get_contents(__DIR__.'/../webhook_examples/pull_request.opened.json')
        );

        $this->assertTrue($this->argumentResolver->supports($request, $metadata));
        $events = iterator_to_array($this->argumentResolver->resolve($request, $metadata));
        $this->assertInstanceOf('AppBundle\Event\GitHubEvent', $events[0]);
    }
}
