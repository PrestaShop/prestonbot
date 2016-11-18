<?php

namespace AppBundle\PullRequests;

use Symfony\Component\Validator\Constraints as Assert;
use Lpdigital\Github\Entity\PullRequest;

/**
 * Extract human readable data from commit.
 */
class CommitParser
{
    /**
     * @Assert\Regex("/^(CO|FO|BO|TE|IN|WS|LO)(\:[[:space:]])(.+)/")
     */
    private $message;
    private $pullRequest;

    public function __construct($message, PullRequest $pullRequest)
    {
        $this->message = $message;
        $this->pullRequest = $pullRequest;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
