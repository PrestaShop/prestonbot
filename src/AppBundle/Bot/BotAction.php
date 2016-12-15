<?php

namespace AppBundle\Bot;

/**
 * Represents every action of Preston
 */
class BotAction
{
    private $id;

    private $name;

    private $eventName;

    private $callbackName;

    private $enabled;

    public function enable()
    {
        $this->enabled = true;

        return $this;
    }

    public function disable()
    {
        $this->enabled = false;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getEventName()
    {
        return $this->eventName;
    }

    public function setEventName($eventName)
    {
        $this->eventName = $eventName;

        return $this;
    }

    public function getCallbackName()
    {
        return $this->callbackName;
    }

    public function setCallbackName($callbackName)
    {
        $this->callbackName = $callbackName;

        return $this;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }
}
