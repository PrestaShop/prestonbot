<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Event\GitHubEvent;

class IssuesSubscriber implements EventSubscriberInterface
{
    public $container;
    
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public static function getSubscribedEvents()
    {
        return array(
           'issuesevent_labeled' => array(
               array('updateLabels', 255),
           )
        );
    }
    
    /**
     * Changes "Bug" issues to "Needs Review".
     */
    public function updateLabels(GitHubEvent $githubEvent)
    {
        if(true === $this->container->getParameter('enable_labels')) {
            $event = $githubEvent->getEvent();

            $status = $this->container
                ->get('app.issue_listener')
                ->handleLabelAddedEvent(
                    $event->issue->getNumber(),
                    $event->label->getName()
            );
            
            $action = (null === $status) ? 'ignored' : 'added required labels';
            $githubEvent->addStatus([
                'event' => 'issue_event_labeled',
                'action' => $action,
            ]);
        }
    }
}