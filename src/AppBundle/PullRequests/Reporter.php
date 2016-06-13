<?php

namespace AppBundle\PullRequests;

/**
 * Returns useful informations about Pull requests status.
 */
class Reporter
{
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function reportActivity()
    {
        $toBeCodeReviewed = $this->findAll(Labels::WAITING_FOR_CODE_REVIEW);
        $toBeQAFeedback = $this->findAll(Labels::WAITING_FOR_QA_FEEDBACK);
        $toBePMFeedback = $this->findAll(Labels::WAITING_FOR_PM_FEEDBACK);
        $silentContribs = $this->findAll('');

        return [
            'waitingForCodeReviewsContribs' => $toBeCodeReviewed,
            'waitingForQAContribs' => $toBeQAFeedback,
            'waitingForPMContribs' => $toBePMFeedback,
            'silentContribs' => $silentContribs,
        ];
    }

    private function findAll($tagName)
    {
        return $this->repository->findAllWithTag($tagName);
    }
}
