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
        return [
           'pullrequestevent_opened' => [
               ['checkForTableDescription', 255],
               ['initLabels', 254],
               ['welcomePeople', 253],
           ],
           'pullrequestevent_edited' => [
               ['removePrestonComment', 255],
            ],
        ];
    }

    /**
     * For now, only add "Needs Review" label.
     */
    public function initLabels(GitHubEvent $githubEvent)
    {
        $event = $githubEvent->getEvent();

        if (true === $this->container->getParameter('labels_pr_creation')) {
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
    }

    public function checkForTableDescription(GitHubEvent $githubEvent)
    {
        $event = $githubEvent->getEvent();
        $pullRequest = $event->pullRequest;

        if (
            'closed' === $pullRequest->getState() ||
            (null !== $pullRequest->isMerged() && true === $pullRequest->isMerged())

        ) {
            return;
        }

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

    public function removePrestonComment(GithubEvent $githubEvent)
    {
        $githubEvent->addStatus([
            'event' => 'pr_edited',
            'action' => 'preston validation comment removed',
            ])
        ;
    }
}
