<?php

namespace AppBundle\PullRequests;

/**
 * Returns useful informations about Pull requests status.
 */
class Reporter
{
    private $repository;

    private $labelToVarname = [
        Labels::WAITING_FOR_CODE_REVIEW => 'waitingForCodeReviewsContribs',
        Labels::WAITING_FOR_QA_FEEDBACK => 'waitingForQAContribs',
        Labels::WAITING_FOR_PM_FEEDBACK => 'waitingForPMContribs',
    ];

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

    public function reportActivityForLabel($base = 'develop', $label = Labels::WAITING_FOR_CODE_REVIEW)
    {
        if (!in_array($label, array_keys($this->labelToVarname))) {
            throw new LabelNotFoundException($label);
        }

        $varName = $this->labelToVarname[$label];

        return [
            $varName => $this->findAll($label, $base),
        ];
    }

    private function findAll($tagName, $base)
    {
        return $this->repository->findAllWithLabel($tagName, $base);
    }
}
