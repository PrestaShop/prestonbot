<?php

namespace AppBundle\Event;

use InvalidArgumentException;
use PrestaShop\Github\Event\GithubEventInterface;
use PrestaShop\Github\WebhookHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GitHubEventResolver implements ArgumentValueResolverInterface
{
    /**
     * @var WebhookHandler
     */
    private $webhookHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $repositoryOwner;

    /**
     * @var string
     */
    private $repositoryName;

    public function __construct(
        WebhookHandler $webhookHandler,
        LoggerInterface $logger,
        string $repositoryOwner,
        string $repositoryName
    ) {
        $this->webhookHandler = $webhookHandler;
        $this->logger = $logger;
        $this->repositoryOwner = $repositoryOwner;
        $this->repositoryName = $repositoryName;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return GitHubEvent::class === $argument->getType();
    }

    /**
     * @param Request          $request
     * @param ArgumentMetadata $argument
     *
     * @return \Generator
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $payload = json_decode($request->getContent(), true);
        $githubEvent = null;

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException('Invalid JSON body');
        }

        $event = $this->webhookHandler->handle($payload);
        if (null === $event || null === $event->getAction() || !$this->isValid($event)) {
            $this->logger->error(
                sprintf(
                    '[Event] %s received from `%s` repository',
                    $event::name(),
                    $event->getRepository()->getFullName()
                )
            );
            throw new NotFoundHttpException();
        }

        $githubEvent = new GitHubEvent($event::name(), $event);

        yield $githubEvent;
    }

    /**
     * @param GithubEventInterface $event
     *
     * @return bool
     */
    private function isValid(GithubEventInterface $event): bool
    {
        [$repositoryUsername, $repositoryName] = explode('/', $event->getRepository()->getFullName());

        return $repositoryUsername === $this->repositoryOwner && $repositoryName === $this->repositoryName;
    }
}
