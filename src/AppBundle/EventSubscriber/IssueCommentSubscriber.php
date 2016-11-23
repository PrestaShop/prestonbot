<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Event\GitHubEvent;

class IssueCommentSubscriber implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
           'issuecommentevent_created' => [
               ['addLabels', 255],
           ],
        ];
    }

    /**
     * @param GitHubEvent $githubEvent
     */
    public function addLabels(GitHubEvent $githubEvent)
    {
        if (true === $this->container->getParameter('enable_labels')) {
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
