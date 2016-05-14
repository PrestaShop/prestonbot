<?php

namespace AppBundle\PullRequests;

use Github\Api\Issue;
use Lpdigital\Github\Entity\PullRequest;

class Listener
{
    private $issueApi;
    private $repositoryUsername;
    private $repositoryName;
    
    public function __construct(Issue $issue, $repositoryUsername, $repositoryName)
    {
        $this->issueApi = $issue;
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
    }
    
    public function checkForDescription(PullRequest $pullRequest, $commitId)
    {
        $this->issueApi->comments()
            ->create($this->repositoryUsername, $this->repositoryName, $pullRequest->getNumber(), [
                'body' => 'created from describe botterland',
                ]
            );
    }
}
