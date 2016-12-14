<?php

namespace AppBundle\Event;

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

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ActionableEventInterface
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return array
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * @param array $status
     */
    public function addStatus(array $status)
    {
        $this->statuses[] = $status;
    }
}
