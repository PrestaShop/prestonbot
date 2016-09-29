<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Event\GitHubEvent;

class PullRequestSubscriber implements EventSubscriberInterface
{
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return [
           'pullrequestevent_opened' => [
               ['checkForTableDescription', 255],
               ['welcomePeople', 253],
               ['initLabels', 254],
               ['checkCommits', 252],
           ],
           'pullrequestevent_edited' => [
               ['removePullRequestValidationComment', 255],
               ['removePullCommitValidationComment', 255],
            ],
        ];
    }

    /**
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
     * Validate the commits name.
     */
    public function checkCommits(GitHubEvent $githubEvent)
    {
        $pullRequest = $githubEvent->getEvent()->pullRequest;

        $this->container
            ->get('app.pullrequest_listener')
            ->checkCommits($pullRequest)
        ;

        $githubEvent->addStatus([
            'event' => 'pr_opened',
            'action' => 'commits labels checked',
            ])
        ;
    }

    public function welcomePeople(GitHubEvent $githubEvent)
    {
        // use https://github.com/KnpLabs/php-github-api/blob/master/lib/Github/Api/Repo.php and implement a global maybe ?
        // PrestaShop/Repository.php

        $githubEvent->addStatus([
            'event' => 'pr_opened',
            'action' => 'user welcomed',
            ])
        ;
    }

    /**
     * @todo: create functional test in WebhookController
     */
    public function removePullRequestValidationComment(GithubEvent $githubEvent)
    {
        $pullRequest = $githubEvent->getEvent()->pullRequest;

        if ($pullRequest->isClosed() || $pullRequest->isMerged()) {
            return;
        }

        $this->container
            ->get('app.pullrequest_listener')
            ->handleValidationMessagesOnEdition($pullRequest)
        ;

        $githubEvent->addStatus([
            'event' => 'pr_edited',
            'action' => 'preston validation comment removed',
            ])
        ;
    }
}
