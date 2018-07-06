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
    private $repositoryOwner;

    /**
     * @var string
     */
    private $repositoryName;

    public function __construct(Labels $labelsApi, $repositoryOwner, $repositoryName)
    {
        $this->labelsApi = $labelsApi;
        $this->repositoryOwner = $repositoryOwner;
        $this->repositoryName = $repositoryName;
    }

    /**
     * @param int $issueNumber
     *
     * @return array
     */
    public function getIssueLabels(int $issueNumber)
    {
        if (!isset($this->labelCache[$issueNumber])) {
            $this->labelCache[$issueNumber] = [];

            $labelsData = $this->labelsApi->all(
                $this->repositoryOwner,
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
     * @param int    $issueNumber
     * @param string $label
     */
    public function addIssueLabel(int $issueNumber, string $label)
    {
        if (isset($this->labelCache[$issueNumber][$label])) {
            return;
        }

        $this->labelsApi->add(
            $this->repositoryOwner,
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
     * @param int    $issueNumber
     * @param string $label
     */
    public function removeIssueLabel(int $issueNumber, string $label)
    {
        if (isset($this->labelCache[$issueNumber]) && !isset($this->labelCache[$issueNumber][$label])) {
            return;
        }

        $this->labelsApi->remove(
            $this->repositoryOwner,
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
