<?php

namespace AppBundle\Event;

use InvalidArgumentException;
use Lpdigital\Github\EventType\ActionableEventInterface;
use Lpdigital\Github\EventType\GithubEventInterface;
use Lpdigital\Github\Parser\WebhookResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GitHubEventResolver implements ArgumentValueResolverInterface
{
    /**
     * @var WebhookResolver
     */
    private $resolver;

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
        WebhookResolver $resolver,
        LoggerInterface $logger,
        string $repositoryOwner,
        string $repositoryName
    ) {
        $this->resolver = $resolver;
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
     *
     * @throws \Lpdigital\Github\Exception\EventNotFoundException
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $payload = json_decode($request->getContent(), true);
        $githubEvent = null;

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException('Invalid JSON body');
        }

        $event = $this->resolver->resolve($payload);

        if ($event instanceof ActionableEventInterface &&
            $event instanceof GithubEventInterface &&
            $this->isValid($event)
        ) {
            $githubEvent = new GitHubEvent($event::name(), $event);
        } else {
            $this->logger->error(
                sprintf(
                    '[Event] %s received from `%s` repository',
                    $event::name(),
                    $event->getRepository()->getFullName()
                )
            );

            throw new NotFoundHttpException();
        }

        yield $githubEvent;
    }

    /**
     * @param ActionableEventInterface $event
     *
     * @return bool
     */
    private function isValid(ActionableEventInterface $event): bool
    {
        [$repositoryUsername, $repositoryName] = explode('/', $event->getRepository()->getFullName());

        return $repositoryUsername === $this->repositoryOwner && $repositoryName === $this->repositoryName;
    }
}
