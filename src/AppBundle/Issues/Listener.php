<?php

namespace AppBundle\Issues;

use AppBundle\PullRequests\BodyParser;
use AppBundle\PullRequests\Labels;
use PrestaShop\Github\Entity\PullRequest;
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
        $this->statusApi->removeIssueLabel($issueNumber, Labels::WORDING_APPROVED);
        $this->log($issueNumber, $newStatus);

        return $newStatus;
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

        // only add labels if they are defined in the white list
        if (isset(Labels::ALIASES[$pullRequestType])) {
            $this->statusApi->addIssueLabel($issueNumber, $pullRequestType);
            $this->log($issueNumber, $pullRequestType);
        }
    }

    /**
     * Add "BC break" label to an issue if declared in the issue description table.
     *
     * @param PullRequest $pullRequest
     *
     * @return bool
     */
    public function addBackwardCompatibleLabel(PullRequest $pullRequest): bool
    {
        $bodyParser = new BodyParser($pullRequest->getBody());
        $issueNumber = $pullRequest->getNumber();

        if (!$bodyParser->isBackwardCompatible()) {
            $this->statusApi->addIssueLabel($issueNumber, Labels::BC_BREAK);
            $this->log($issueNumber, Labels::BC_BREAK);

            return true;
        }

        return false;
    }

    /**
     * Add "PR available" label to an issue if declared as issue fixed in the PR.
     *
     * @param PullRequest $pullRequest
     *
     * @return bool
     */
    public function addPRAvailableLabel(PullRequest $pullRequest): bool
    {
        $bodyParser = new BodyParser($pullRequest->getBody());
        $issueNumber = $bodyParser->getRelatedTicket();
        if (!empty($issueNumber)) {
            $this->statusApi->addIssueLabel(substr($issueNumber, 1), Labels::PR_AVAILABLE);
            $this->log($issueNumber, Labels::PR_AVAILABLE);

            return true;
        }

        return false;
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
