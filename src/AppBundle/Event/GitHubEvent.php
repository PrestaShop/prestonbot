<?php

namespace AppBundle\Event;

use PrestaShop\Github\Entity\PullRequest;
use PrestaShop\Github\Event\GithubEventInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Every event returned by GitHub is stored as a GitHubEvent instance.
 */
class GitHubEvent extends Event
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var GithubEventInterface
     */
    private $event;
    /**
     * @var array
     */
    private $statuses;

    public function __construct(string $name, GithubEventInterface $event)
    {
        $this->name = $name;
        $this->event = $event;
        $this->statuses = [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEvent(): GithubEventInterface
    {
        return $this->event;
    }

    public function getPullRequest(): ?PullRequest
    {
        return method_exists($this->event, 'getPullRequest') ? $this->event->getPullRequest() : null;
    }

    public function getStatuses(): array
    {
        return $this->statuses;
    }

    public function addStatus(array $status)
    {
        $this->statuses[] = $status;
    }
}
