<?php

namespace AppBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Every event returned by GitHub is stored as a GitHubEvent instance
 */
class GitHubEvent extends Event
{
    private $name;
    private $event;
    private $statuses;
    
    public function __construct($name, $event)
    {
        $this->name = $name;
        $this->event = $event;
        $this->statuses = [];
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getEvent()
    {
        return $this->event;
    }
    
    public function getStatuses()
    {
        return $this->statuses;
    }
    
    public function addStatus($status)
    {
        $this->statuses[] = $status;
    }
}
