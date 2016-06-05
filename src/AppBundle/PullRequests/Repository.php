<?php

namespace AppBundle\PullRequests;

use Github\Api\Issue;
use Lpdigital\Github\Entity\PullRequest;

/**
 * Get the pull requests according to some filters
 * As GitHub consider pull requests as specific issues
 * don't be surprised too much by the produced repository.
 * 
 * @doc https://github.com/KnpLabs/php-github-api/blob/master/doc/issues.md
 * @todo: how to test this feature easily ? :/ prepare a fake issueApi
 */
class Repository
{
    private $issueApi;
    private $repositoryUsername;
    private $repositoryName;
    
    public function __construct(Issue $issueApi, $repositoryUsername, $repositoryName)
    {
        $this->issueApi = $issueApi;
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
    }
    
    public function findAll()
    {
        $pullRequests = [];
        $issues = $this->issueApi->all($this->repositoryUsername, $this->repositoryName, []);
        
        /* @doc https://developer.github.com/v3/pulls/#labels-assignees-and-milestones */
        foreach($issues as $issue) {
            if (isset($issue['pull_request'])) {
                $pullRequests[] = PullRequest::createFromData($issue);
            }
        }
        
        return $pullRequests;
    }
    
    public function findAllWithTag($tag)
    {
        $pullRequests = [];
        $issues = $this->issueApi->all($this->repositoryUsername, $this->repositoryName, ['labels' => $tag]);
        
        foreach($issues as $issue) {
            if (isset($issue['pull_request'])) {
                $pullRequests[] = PullRequest::createFromData($issue);
            }
        }
        
        return $pullRequests;
    }
    
    public function findAllWithTags($tags)
    {
        $pullRequests = [];
        $issues = $this->issueApi->all($this->repositoryUsername, $this->repositoryName, ['labels' => implode(",", $tags)]);
        
        foreach($issues as $issue) {
            if (isset($issue['pull_request'])) {
                $pullRequests[] = PullRequest::createFromData($issue);
            }
        }
        
        return $pullRequests;
    }
    
    public function findAllWaitingSince($nbDays)
    {
        throw new \Exception('Need to be done');
        return [];
    }
}
