<?php

namespace AppBundle\Tests\Issues;

use AppBundle\Issues\StatusApi;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NullStatusApi implements StatusApi
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
