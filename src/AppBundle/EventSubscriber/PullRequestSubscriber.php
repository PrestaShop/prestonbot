<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Event\GitHubEvent;
use AppBundle\Diff\Diff;

class PullRequestSubscriber implements EventSubscriberInterface
{
    const TRANS_PATTERN = '#(trans\(|->l\()#';

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
            'pullrequestevent_opened' => [
               ['checkForTableDescription', 255],
               ['welcomePeople', 253],
               ['checkForNewTranslations', 252],
               ['initLabels', 254],
               ['checkCommits', 252],
            ],
            'pullrequestevent_edited' => [
               ['removePullRequestValidationComment', 255],
               ['removeCommitValidationComment', 255],
               ['checkForNewTranslations', 252],
            ],
            'pullrequestevent_synchronize' => [
                ['removeCommitValidationComment', 255],
            ],
        ];
    }

    /**
     * @param GitHubEvent $githubEvent
     *
     * For now, only add "Needs Review" label.
     */
    public function initLabels(GitHubEvent $githubEvent)
    {
        $pullRequest = $githubEvent->getEvent()->pullRequest;

        if (true === $this->container->getParameter('labels_pr_creation')) {
            $this->container
                ->get('app.issue_listener')
                ->handlePullRequestCreatedEvent($pullRequest->getNumber())
            ;

            $githubEvent->addStatus([
                'event' => 'pr_opened',
                'action' => 'labels initialized',
                ])
            ;
        }
    }

    /**
     * @param GitHubEvent $githubEvent
     *
     * This event MUST be spawned first.
     */
    public function checkForTableDescription(GitHubEvent $githubEvent)
    {
        $pullRequest = $githubEvent->getEvent()->pullRequest;

        $this->container
            ->get('app.pullrequest_listener')
            ->checkForTableDescription($pullRequest)
        ;

        $githubEvent->addStatus([
            'event' => 'pr_opened',
            'action' => 'table description checked',
            ])
        ;
    }

    /**
     * @param GitHubEvent $githubEvent
     *
     * Validate the commits labels.
     */
    public function checkCommits(GitHubEvent $githubEvent)
    {
        $pullRequest = $githubEvent->getEvent()->pullRequest;

        $hasErrors = $this->container
            ->get('app.pullrequest_listener')
            ->checkCommits($pullRequest)
        ;

        $githubEvent->addStatus([
            'event' => 'pr_opened',
            'action' => 'commits labels checked',
            'status' => $hasErrors ? 'not_valid' : 'valid',
        ]);
    }

    /**
     * @param GitHubEvent $githubEvent
     *
     * If a call to trans or l function is done, add
     * "waiting for wording" label.
     */
    public function checkForNewTranslations(GitHubEvent $githubEvent)
    {
        $event = $githubEvent->getEvent();
        $pullRequest = $event->pullRequest;
        $diff = Diff::create(file_get_contents($pullRequest->getDiffUrl()));

        if ($found = $diff->additions()->contains(self::TRANS_PATTERN)->match()) {
            $this->container
                ->get('app.issue_listener')
                ->handleWaitingForWordingEvent($pullRequest->getNumber())
            ;
        }

        $eventStatus = $event->getAction() == 'opened' ? 'opened' : 'edited';

        $githubEvent->addStatus([
            'event' => 'pr_'.$eventStatus,
            'action' => 'checked for new translations',
            'status' => $found ? 'found' : 'not_found',
            ])
        ;
    }

    /**
     * @param GitHubEvent $githubEvent
     *
     * Send a comment to welcome very first contribution.
     */
    public function welcomePeople(GitHubEvent $githubEvent)
    {
        $pullRequest = $githubEvent->getEvent()->pullRequest;
        $sender = $githubEvent->getEvent()->sender;

        $this->container
            ->get('app.pullrequest_listener')
            ->welcomePeople($pullRequest, $sender)
        ;

        $githubEvent->addStatus([
            'event' => 'pr_opened',
            'action' => 'user welcomed',
            ])
        ;
    }

    /**
     * @param GitHubEvent $githubEvent
     *
     * If description become valid, the comment should be removed.
     */
    public function removePullRequestValidationComment(GithubEvent $githubEvent)
    {
        $pullRequest = $githubEvent->getEvent()->pullRequest;

        if ($pullRequest->isClosed() || $pullRequest->isMerged()) {
            return;
        }

        $success = $this->container
            ->get('app.pullrequest_listener')
            ->removePullRequestValidationComment($pullRequest)
        ;

        if ($success) {
            $githubEvent->addStatus([
                'event' => 'pr_edited',
                'action' => 'preston validation comment removed',
                ])
            ;
        }
    }

    /**
     * @param GitHubEvent $githubEvent
     *
     * If commits labels become valid, the comment should be removed.
     */
    public function removeCommitValidationComment(GithubEvent $githubEvent)
    {
        $pullRequest = $githubEvent->getEvent()->pullRequest;

        if ($pullRequest->isClosed() || $pullRequest->isMerged()) {
            return;
        }

        $success = $this->container
            ->get('app.pullrequest_listener')
            ->removeCommitValidationComment($pullRequest)
        ;

        if ($success) {
            $githubEvent->addStatus([
                'event' => 'pr_edited',
                'action' => 'preston validation commit comment removed',
                ])
            ;
        }
    }
}
