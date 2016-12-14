<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Event\GitHubEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class IssueSubscriber implements EventSubscriberInterface
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
           'issuesevent_labeled' => [
               ['updateLabels', 255],
           ],
        ];
    }

    /**
     * Changes "Bug" issues to "Needs Review".
     */
    public function updateLabels(GitHubEvent $githubEvent)
    {
        if (true === $this->container->getParameter('enable_labels')) {
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
