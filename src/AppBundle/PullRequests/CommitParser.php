<?php

namespace AppBundle\PullRequests;

use Lpdigital\Github\Entity\PullRequest;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Extract human readable data from commit.
 */
class CommitParser
{
    /**
     * @Assert\Regex("/^(CO|FO|BO|TE|IN|WS|LO)(\:\s+)(.+)/")
     *
     * @var string
     */
    private $message;

    /**
     * @var PullRequest
     */
    private $pullRequest;

    public function __construct(string $message, PullRequest $pullRequest)
    {
        $this->message = $message;
        $this->pullRequest = $pullRequest;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return PullRequest
     */
    public function getPullRequest()
    {
        return $this->pullRequest;
    }
}
