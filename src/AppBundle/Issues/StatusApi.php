<?php

namespace AppBundle\Issues;

use AppBundle\PullRequests\Labels;

class StatusApi
{
    /**
     * @var CachedLabelsApi
     */
    private $labelsApi;

    /**
     * @var string
     */
    private $repositoryOwner;

    /**
     * @var string
     */
    private $repositoryName;

    public function __construct(CachedLabelsApi $labelsApi, $repositoryOwner, $repositoryName)
    {
        $this->labelsApi = $labelsApi;
        $this->repositoryOwner = $repositoryOwner;
        $this->repositoryName = $repositoryName;
    }

    /**
     * @param int    $issueNumber The GitHub issue number
     * @param string $newLabel    A Status::* constant
     *
     * @return bool
     */
    public function addIssueLabel($issueNumber, $newLabel)
    {
        if (isset(Labels::ALIASES[$newLabel])) {
            $newLabel = Labels::ALIASES[$newLabel];
        }

        $this->labelsApi->addIssueLabel($issueNumber, $newLabel);

        return true;
    }

    /**
     * @param int    $issueNumber The GitHub issue number
     * @param string $label       A Status::* constant
     *
     * @return bool
     */
    public function removeIssueLabel($issueNumber, $label)
    {
        if (isset(Labels::ALIASES[$label])) {
            $label = Labels::ALIASES[$label];
        }

        try {
            $this->labelsApi->removeIssueLabel($issueNumber, $label);
        } catch (\Exception $e) {
            // The Issue didn't have the label already
        }

        return true;
    }

    /**
     * @return string
     */
    public function getNeedsReviewUrl()
    {
        return sprintf(
            'https://github.com/%s/%s/labels/%s',
            $this->repositoryOwner,
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
            $this->repositoryOwner,
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
            $this->repositoryOwner,
            $this->repositoryName,
            rawurlencode(Labels::WAITING_FOR_PM_FEEDBACK)
        );
    }
}
