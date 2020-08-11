<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Diff\Diff;
use AppBundle\Event\GitHubEvent;
use AppBundle\Issues\Listener as IssuesListener;
use AppBundle\PullRequests\Listener as PullRequestsListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PullRequestSubscriber implements EventSubscriberInterface
{
    const CLASSIC_PATH = '#^themes\/classic\/#';

    /**
     * @var IssuesListener
     */
    private $issuesListener;

    /**
     * @var PullRequestsListener
     */
    private $pullRequestsListener;

    public function __construct(IssuesListener $issuesListener, PullRequestsListener $pullRequestsListener)
    {
        $this->issuesListener = $issuesListener;
        $this->pullRequestsListener = $pullRequestsListener;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'pullrequestevent_opened' => [
                ['checkForTableDescription', 254],
                ['welcomePeople', 255],
                ['checkForNewTranslations', 252],
                ['initBranchLabel', 254],
                ['initPullRequestTypeLabel', 254],
                ['checkIfPrFixCriticalIssue', 253],
            ],
            'pullrequestevent_edited' => [
                ['removePullRequestValidationComment', 255],
                ['initBranchLabel', 254],
                ['initPullRequestTypeLabel', 254],
            ],
            'pullrequestevent_synchronize' => [
                ['checkForNewTranslations', 252],
            ],
        ];
    }

    /**
     * @param gitHubEvent $githubEvent
     *
     * Add the branch label according to the branch selected in PR template
     */
    public function initBranchLabel(GitHubEvent $githubEvent)
    {
        $this->issuesListener->addBranchLabel($githubEvent->getPullRequest());

        $githubEvent->addStatus([
            'event' => 'pr_opened',
            'action' => 'branch label initialized',
        ]);
    }

    /**
     * @param gitHubEvent $githubEvent
     *
     * Add the pull request type according to the type selected in PR template
     */
    public function initPullRequestTypeLabel(GitHubEvent $githubEvent)
    {
        $this->issuesListener->addPullRequestTypeLabel($githubEvent->getPullRequest());

        $githubEvent->addStatus([
            'event' => 'pr_opened',
            'action' => 'pr type label initialized',
        ]);
    }

    /**
     * @param GitHubEvent $githubEvent
     *
     * This event MUST be spawned second
     */
    public function checkForTableDescription(GitHubEvent $githubEvent)
    {
        $this->pullRequestsListener->checkForTableDescription($githubEvent->getPullRequest());

        $githubEvent->addStatus([
            'event' => 'pr_opened',
            'action' => 'table description checked',
        ]);
    }

    /**
     * @param GitHubEvent $githubEvent
     *
     * If a call to trans or l function is done, add
     * "waiting for wording" label
     */
    public function checkForNewTranslations(GitHubEvent $githubEvent)
    {
        $pullRequest = $githubEvent->getPullRequest();
        $event = $githubEvent->getEvent();
        $newWording = $this->pullRequestsListener->checkForNewTranslations($pullRequest);

        if ($newWording) {
            $this->issuesListener->handleWaitingForWordingEvent($pullRequest->getNumber());
        }

        $eventStatus = 'opened' === $event->getAction() ? 'opened' : 'edited';

        $githubEvent->addStatus([
            'event' => 'pr_'.$eventStatus,
            'action' => 'checked for new translations',
            'status' => $newWording ? 'found' : 'not_found',
        ]);
    }

    public function checkIfPrFixCriticalIssue(GitHubEvent $githubEvent)
    {
        $labelWasAdded = $this->issuesListener->addLabelCriticalLabelIfNeeded($githubEvent->getPullRequest());

        if ($labelWasAdded) {
            $githubEvent->addStatus([
                'event' => 'pr_created',
                'action' => 'critical label was added',
            ]);
        }
    }

    /**
     * @param GitHubEvent $githubEvent
     *
     * If a change occurs in one of classic's files, add
     * "report on StarterTheme" label
     */
    public function checkForClassicChanges(GitHubEvent $githubEvent)
    {
        $event = $githubEvent->getEvent();
        $pullRequest = $githubEvent->getPullRequest();
        $diff = Diff::create(file_get_contents($pullRequest->getDiffUrl()));

        if ($found = $diff->path(self::CLASSIC_PATH)->match()) {
            $this->issuesListener->handleClassicChangesEvent($pullRequest->getNumber());
        }

        $eventStatus = 'opened' === $event->getAction() ? 'opened' : 'edited';

        $githubEvent->addStatus([
            'event' => 'pr_'.$eventStatus,
            'action' => 'checked for changes on Classic Theme',
            'status' => $found ? 'found' : 'not_found',
        ]);
    }

    /**
     * @param GitHubEvent $githubEvent
     *
     * This event MUST be spawned second.
     * Send a comment to welcome very first contribution.
     */
    public function welcomePeople(GitHubEvent $githubEvent)
    {
        $sender = $githubEvent->getEvent()->sender;

        $this->pullRequestsListener->welcomePeople($githubEvent->getPullRequest(), $sender);

        $githubEvent->addStatus([
            'event' => 'pr_opened',
            'action' => 'user welcomed',
        ]);
    }

    /**
     * @param GitHubEvent $githubEvent
     *
     * If description become valid, the comment should be removed
     */
    public function removePullRequestValidationComment(GithubEvent $githubEvent)
    {
        $pullRequest = $githubEvent->getPullRequest();

        if ($pullRequest->isClosed() || $pullRequest->isMerged()) {
            return;
        }

        $success = $this->pullRequestsListener->removePullRequestValidationComment($pullRequest);

        if ($success) {
            $githubEvent->addStatus([
                'event' => 'pr_edited',
                'action' => 'preston validation comment removed',
            ]);
        }
    }
}
