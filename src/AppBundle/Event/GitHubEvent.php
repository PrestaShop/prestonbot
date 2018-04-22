<?php

namespace AppBundle\Event;

use Lpdigital\Github\Entity\PullRequest;
use Lpdigital\Github\EventType\ActionableEventInterface;
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
     * @var \Lpdigital\Github\EventType\ActionableEventInterface
     */
    private $event;
    /**
     * @var array
     */
    private $statuses;

    public function __construct(string $name, ActionableEventInterface $event)
    {
        $this->name = $name;
        $this->event = $event;
        $this->statuses = [];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEvent(): ActionableEventInterface
    {
        return $this->event;
    }

    public function getPullRequest(): ?PullRequest
    {
        return property_exists($this->event, 'pullRequest') ? $this->event->pullRequest : null;
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
