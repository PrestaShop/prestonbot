<?php

namespace AppBundle\PullRequests;

/**
 * Returns useful informations about Pull requests status.
 */
class Reporter
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var array
     */
    private $labelToVarname = [
        Labels::WAITING_FOR_CODE_REVIEW => 'waitingForCodeReviewsContribs',
        Labels::WAITING_FOR_QA_FEEDBACK => 'waitingForQAContribs',
        Labels::WAITING_FOR_PM_FEEDBACK => 'waitingForPMContribs',
    ];

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $base
     *
     * @return array
     */
    public function reportActivity(string $base = 'develop')
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

    /**
     * @param string $base
     * @param string $label
     *
     * @return array
     *
     * @throws LabelNotFoundException
     */
    public function reportActivityForLabel(string $base = 'develop', string $label = Labels::WAITING_FOR_CODE_REVIEW)
    {
        if (!in_array($label, array_keys($this->labelToVarname))) {
            throw new LabelNotFoundException($label);
        }

        $varName = $this->labelToVarname[$label];

        return [
            $varName => $this->findAll($label, $base),
        ];
    }

    /**
     * @param $tagName
     * @param $base
     *
     * @return array
     */
    private function findAll(string $tagName, string $base)
    {
        return $this->repository->findAllWithLabel($tagName, $base);
    }
}
