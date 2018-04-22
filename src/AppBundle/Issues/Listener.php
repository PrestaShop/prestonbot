<?php

namespace AppBundle\Issues;

use AppBundle\PullRequests\BodyParser;
use Lpdigital\Github\Entity\PullRequest;
use Psr\Log\LoggerInterface;

class Listener
{
    /**
     * @var StatusApi
     */
    private $statusApi;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(StatusApi $statusApi, LoggerInterface $logger)
    {
        $this->statusApi = $statusApi;
        $this->logger = $logger;
    }

    /**
     * Add "waiting for wording" label to an issue.
     *
     * @param int $issueNumber The issue that was labeled
     *
     * @return string The new status
     */
    public function handleWaitingForWordingEvent($issueNumber): string
    {
        $newStatus = Status::WAITING_FOR_WORDING;

        $this->statusApi->addIssueLabel($issueNumber, $newStatus);
        $this->log($issueNumber, $newStatus);

        return $newStatus;
    }

    /**
     * @param PullRequest $pullRequest
     *
     * @return bool
     */
    public function addLabelCriticalLabelIfNeeded(PullRequest $pullRequest): bool
    {
        $bodyParser = new BodyParser($pullRequest->getBody());

        if ('critical' === $bodyParser->getType()) {
            $prNumber = $pullRequest->getNumber();
            $newStatus = Status::CRITICAL_ISSUE;
            $this->statusApi->addIssueLabel($prNumber, $newStatus);
            $this->log($prNumber, $newStatus);

            return true;
        }

        return false;
    }

    /**
     * Add a label for a branch described in Pull Request template.
     *
     * @param PullRequest $pullRequest
     *
     * @return bool
     */
    public function addBranchLabel(PullRequest $pullRequest): bool
    {
        $bodyParser = new BodyParser($pullRequest->getBody());
        $issueNumber = $pullRequest->getNumber();
        $branch = trim($bodyParser->getBranch());

        if (\in_array($branch, Status::$branches, true)) {
            $this->statusApi->addIssueLabel($issueNumber, $branch);
            $this->log($issueNumber, $branch);

            return true;
        }

        return false;
    }

    /**
     * Add a label for a type described in Pull Request template.
     *
     * @param PullRequest $pullRequest
     */
    public function addPullRequestTypeLabel(PullRequest $pullRequest)
    {
        $bodyParser = new BodyParser($pullRequest->getBody());
        $issueNumber = $pullRequest->getNumber();
        $pullRequestType = trim($bodyParser->getType());

        $this->statusApi->addIssueLabel($issueNumber, $pullRequestType);
        $this->log($issueNumber, $pullRequestType);
    }

    /**
     * Add "report on StarterTheme" label to an issue.
     *
     * @param int $issueNumber The issue that was labeled
     *
     * @return string The new status
     */
    public function handleClassicChangesEvent($issueNumber): string
    {
        $newStatus = Status::REPORT_ON_STARTER_THEME;

        $this->statusApi->addIssueLabel($issueNumber, $newStatus);
        $this->log($issueNumber, $newStatus);

        return $newStatus;
    }

    /**
     * Log every label added.
     *
     * @param int    $issueNumber The issue that was labeled
     * @param string $status      The added label
     */
    private function log($issueNumber, $status)
    {
        $this->logger->info(sprintf('[Label] Issue n° %s is labelized with `%s` status',
            $issueNumber,
            $status
        ));
    }
}
