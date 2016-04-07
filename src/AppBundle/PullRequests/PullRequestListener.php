<?php

namespace AppBundle\PullRequests;

use Github\Api\Issue;

class PullRequestListener
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
    
    public function checkForCommitLabel($pullRequestId, $commitId)
    {
        $this->issueApi->comments()
            ->create($this->repositoryUsername, $this->repositoryName, $pullRequestId, [
                'body' => 'created from boterland',
                ]
            );
    }
}
