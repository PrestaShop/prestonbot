<?php

namespace AppBundle\Issues;

use Github\Api\Issue\Labels;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CachedLabelsApi
{
    /**
     * @var Labels
     */
    private $labelsApi;

    /**
     * @var string[][]
     */
    private $labelCache = [];

    /**
     * @var string
     */
    private $repositoryUsername;

    /**
     * @var string
     */
    private $repositoryName;

    public function __construct(Labels $labelsApi, $repositoryUsername, $repositoryName)
    {
        $this->labelsApi = $labelsApi;
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
    }

    /**
     * @param $issueNumber
     *
     * @return array
     */
    public function getIssueLabels($issueNumber)
    {
        if (!isset($this->labelCache[$issueNumber])) {
            $this->labelCache[$issueNumber] = [];

            $labelsData = $this->labelsApi->all(
                $this->repositoryUsername,
                $this->repositoryName,
                $issueNumber
            );

            // Load labels, keep only the first status label
            foreach ($labelsData as $labelData) {
                $this->labelCache[$issueNumber][$labelData['name']] = true;
            }
        }

        return array_keys($this->labelCache[$issueNumber]);
    }

    /**
     * @param $issueNumber
     * @param $label
     */
    public function addIssueLabel($issueNumber, $label)
    {
        if (isset($this->labelCache[$issueNumber][$label])) {
            return;
        }

        $this->labelsApi->add(
            $this->repositoryUsername,
            $this->repositoryName,
            $issueNumber,
            $label
        );

        // Update cache if already loaded
        if (isset($this->labelCache[$issueNumber])) {
            $this->labelCache[$issueNumber][$label] = true;
        }
    }

    /**
     * @param $issueNumber
     * @param $label
     */
    public function removeIssueLabel($issueNumber, $label)
    {
        if (isset($this->labelCache[$issueNumber]) && !isset($this->labelCache[$issueNumber][$label])) {
            return;
        }

        $this->labelsApi->remove(
            $this->repositoryUsername,
            $this->repositoryName,
            $issueNumber,
            $label
        );

        // Update cache if already loaded
        if (isset($this->labelCache[$issueNumber])) {
            unset($this->labelCache[$issueNumber][$label]);
        }
    }
}
