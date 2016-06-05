<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Event\GitHubEvent;

class PullRequestSubscriber implements EventSubscriberInterface
{
    public $container;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public static function getSubscribedEvents()
    {
        return array(
           'pullrequestevent_opened' => array(
               array('checkForTableDescription', 255),
               array('initLabels', 254),
           )
        );
    }

    /**
     * For now, only add "Needs Review" label
     */
    public function initLabels(GitHubEvent $githubEvent)
    {
        $event = $githubEvent->getEvent();
        $this->container
            ->get('app.issue_listener')
            ->handlePullRequestCreatedEvent($event->pullRequest->getNumber())
        ;
        
        $githubEvent->addStatus([
            'event' => 'pr_opened',
            'action' => 'labels initialized',
            ])
        ;
    }

    public function checkForTableDescription(GitHubEvent $githubEvent)
    {
        $event = $githubEvent->getEvent();
        $this->container
            ->get('app.pullrequest_listener')
            ->checkForDescription($event->pullRequest, $event->pullRequest->getCommitSha())
        ;

        $githubEvent->addStatus([
            'event' => 'pr_opened',
            'action' => 'table description checked',
            ])
        ;
    }
}
