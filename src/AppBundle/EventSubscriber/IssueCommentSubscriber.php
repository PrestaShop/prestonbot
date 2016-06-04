<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Event\GitHubEvent;

class IssueCommentSubscriber implements EventSubscriberInterface
{
    public $container;
    
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public static function getSubscribedEvents()
    {
        return array(
           'issuecommentevent_created' => array(
               array('addLabels', 255),
           )
        );
    }
    
    public function addLabels(GitHubEvent $githubEvent)
    {
        if(true === $this->container->getParameter('enable_labels')) {
            $event = $githubEvent->getEvent();

            $this->container
                ->get('app.issue_listener')
                ->handleCommentAddedEvent(
                    $event->issue->getNumber(),
                    $event->comment->getBody()
            );
            
            $githubEvent->addStatus([
                'event' => 'issue_comment_created',
                'action' => 'add labels if required',
            ]);
        }
    }
}