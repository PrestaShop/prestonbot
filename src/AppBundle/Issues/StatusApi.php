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
    private $repositoryUsername;

    /**
     * @var string
     */
    private $repositoryName;

    public function __construct(CachedLabelsApi $labelsApi, $repositoryUsername, $repositoryName)
    {
        $this->labelsApi = $labelsApi;
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
    }

    /**
     * @param int $issueNumber The GitHub issue number
     * @param string $newLabel A Status::* constant
     *
     * @return bool
     */
    public function addIssueLabel($issueNumber, $newLabel)
    {
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
