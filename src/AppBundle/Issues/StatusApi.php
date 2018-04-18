<?php

namespace AppBundle\Issues;

use AppBundle\PullRequests\Labels;

class StatusApi
{
    /**
     * @var array
     */
    private $statusToLabel = [
        Status::NEEDS_REVIEW => 'Status: Needs Review',
        Status::CODE_REVIEWED => 'Code reviewed',
        Status::QA_APPROVED => 'QA-approved',
        Status::PM_APPROVED => 'PM-approved',
        Status::WAITING_FOR_WORDING => 'waiting for wording',
        Status::CRITICAL_ISSUE => 'Type: Critical Bug Fix',
        Status::REPORT_ON_STARTER_THEME => 'report on StarterTheme',
        Status::LEGACY_BRANCH => '1.6.1.x',
        Status::CURRENT_BRANCH => '1.7.x',
        Status::BRANCH_173 => '1.7.x',
        Status::BRANCH_174 => '1.7.x',
        Status::BRANCH_175 => '1.7.x',
        Status::BRANCH_176 => '1.7.x',
        Status::BRANCH_177 => '1.7.x',
        Status::BRANCH_178 => '1.7.x',
        Status::BRANCH_179 => '1.7.x',
        Status::FUTURE_BRANCH => '1.8.x',
        Status::DEVELOP_BRANCH => 'develop',
    ];

    /**
     * @var array
     */
    private $labelToStatus = [];

    /**
     * @var CachedLabelsApi
     */
    private $labelsApi;

    /**
     * @var string
     */
    private $repositoryUsername;

    /**
     * @var string
     */
    private $repositoryName;

    public function __construct(CachedLabelsApi $labelsApi, $repositoryUsername, $repositoryName)
    {
        $this->labelsApi = $labelsApi;
        $this->labelToStatus = array_flip($this->statusToLabel);
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
    }

    /**
     * @param int    $issueNumber The GitHub issue number
     * @param string $newStatus   A Status::* constant
     */
    public function addIssueLabel($issueNumber, $newStatus)
    {
        if (!isset($this->statusToLabel[$newStatus])) {
            throw new \InvalidArgumentException(sprintf('Invalid status "%s"', $newStatus));
        }

        $newLabel = $this->statusToLabel[$newStatus];
        $currentLabels = $this->labelsApi->getIssueLabels($issueNumber);
        foreach ($currentLabels as $label) {
            if ($label !== $newLabel) {
                $this->labelsApi->addIssueLabel($issueNumber, $newLabel);

                return true;
            }
        }

        return false;
    }

    /**
     * @param int    $issueNumber The GitHub issue number
     * @param string $newStatus   A Status::* constant
     */
    public function setIssueStatus($issueNumber, $newStatus)
    {
        if (!isset($this->statusToLabel[$newStatus])) {
            throw new \InvalidArgumentException(sprintf('Invalid status "%s"', $newStatus));
        }

        $newLabel = $this->statusToLabel[$newStatus];
        $currentLabels = $this->labelsApi->getIssueLabels($issueNumber);
        $addLabel = true;

        foreach ($currentLabels as $label) {
            // Ignore non-status, except when the bug is reviewed
            // but still marked as unconfirmed.
            if (
                !isset($this->labelToStatus[$label])
                && !(Status::CODE_REVIEWED === $newStatus && 'Unconfirmed' === $label)
            ) {
                continue;
            }

            if ($newLabel === $label) {
                $addLabel = false;
                continue;
            }

            // Remove other statuses
            $this->labelsApi->removeIssueLabel($issueNumber, $label);
        }

        // Ignored if the label is already set
        if ($addLabel) {
            $this->labelsApi->addIssueLabel($issueNumber, $newLabel);
        }
    }

    /**
     * @param $issueNumber
     */
    public function getIssueStatus($issueNumber)
    {
        $currentLabels = $this->labelsApi->getIssueLabels($issueNumber);

        foreach ($currentLabels as $label) {
            if (isset($this->labelToStatus[$label])) {
                return $this->labelToStatus[$label];
            }
        }

        // No status set
    }

    /**
     * @return string
     */
    public function getNeedsReviewUrl()
    {
        return sprintf(
            'https://github.com/%s/%s/labels/%s',
            $this->repositoryUsername,
            $this->repositoryName,
            rawurlencode(Labels::WAITING_FOR_CODE_REVIEW)
        );
    }

    /**
     * @return string
     */
    public function getWaitingForQAUrl()
    {
        return sprintf(
            'https://github.com/%s/%s/labels/%s',
            $this->repositoryUsername,
            $this->repositoryName,
            rawurlencode(Labels::WAITING_FOR_QA_FEEDBACK)
        );
    }

    /**
     * @return string
     */
    public function getWaitingForPMUrl()
    {
        return sprintf(
            'https://github.com/%s/%s/labels/%s',
            $this->repositoryUsername,
            $this->repositoryName,
            rawurlencode(Labels::WAITING_FOR_PM_FEEDBACK)
        );
    }
}
