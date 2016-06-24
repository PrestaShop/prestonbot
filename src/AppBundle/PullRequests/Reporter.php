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

    public function reportActivity($base = 'develop')
    {
        $toBeCodeReviewed = $this->findAll(Labels::WAITING_FOR_CODE_REVIEW, $base);
        $toBeQAFeedback = $this->findAll(Labels::WAITING_FOR_QA_FEEDBACK, $base);
        $toBePMFeedback = $this->findAll(Labels::WAITING_FOR_PM_FEEDBACK, $base);
        $silentContribs = $this->findAll('', $base);

        return [
            'waitingForCodeReviewsContribs' => $toBeCodeReviewed,
            'waitingForQAContribs' => $toBeQAFeedback,
            'waitingForPMContribs' => $toBePMFeedback,
            'silentContribs' => $silentContribs,
        ];
    }

    private function findAll($tagName, $base)
    {
        return $this->repository->findAllWithLabel($tagName, $base);
    }
}
