<?php

namespace tests\AppBundle\Issues;

use AppBundle\Issues\StatusApi;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NullStatusApi extends StatusApi
{
    public function getIssueStatus($issueNumber)
    {
    }

    public function setIssueStatus($issueNumber, $newStatus)
    {
    }

    public function getNeedsReviewUrl()
    {
    }
}
