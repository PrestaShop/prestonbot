<?php

namespace Tests\AppBundle\Issues;

use AppBundle\Issues\StatusApi;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NullStatusApi extends StatusApi
{
    public function getIssueStatus($issueNumber)
    {
    }

    public function addIssueLabel($issueNumber, $newStatus)
    {
    }

    public function setIssueStatus($issueNumber, $newStatus)
    {
    }

    public function getNeedsReviewUrl()
    {
    }
}
