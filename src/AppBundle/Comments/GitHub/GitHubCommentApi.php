<?php

namespace AppBundle\Comments\GitHub;

use Github\Api\Issue;
use Lpdigital\Github\Entity\PullRequest;

/**
 * Responsible of comments publication on repository
 */
class GitHubCommentApi
{
    /**
     * @var Issue
     */
    private $issue;
    
    /**
     * @var string
     */
    private $repositoryUsername;

    /**
     * @var string
     */
    private $repositoryName;
    
    public function __construct(Issue $issue, $repositoryUsername, $repositoryName)
    {
        $this->issue = $issue;
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
    }
    
    public function send(PullRequest $pullRequest, $comment)
    {
        $this->issue
            ->comments()
            ->create(
                $this->repositoryUsername,
                $this->repositoryName,
                $pullRequest->getNumber(), [
                    'body' => $comment,
                ]
            );
    }
}