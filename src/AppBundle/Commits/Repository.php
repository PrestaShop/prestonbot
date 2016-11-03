<?php

namespace AppBundle\Commits;

use Github\Api\GitData\Commits as CommitApi;
use Github\Api\PullRequest as PullRequestApi;
use Lpdigital\Github\Entity\Commit;
use Lpdigital\Github\Entity\PullRequest;

class Repository implements RepositoryInterface
{
    /**
     * @var CommitApi
     */
    private $commitsApi;

    /**
     * @var PullRequestApi
     */
    private $pullRequestApi;

    /**
     * @var string
     */
    private $repositoryUsername;

    /**
     * @var string
     */
    private $repositoryName;

    public function __construct(
        CommitApi $commitsApi,
        PullRequestApi $pullRequestApi,
        $repositoryUsername,
        $repositoryName
        ) {
        $this->commitsApi = $commitsApi;
        $this->pullRequestApi = $pullRequestApi;
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
    }

    public function findAllByPullRequest(PullRequest $pullRequest)
    {
        $responseApi = $this->pullRequestApi->commits(
            $this->repositoryUsername,
            $this->repositoryName,
            $pullRequest->getNumber()
        );

        $commits = [];
        foreach ($responseApi as $commitApi) {
            $commits[] = Commit::createFromData($commitApi['commit']);
        }

        return $commits;
    }
}
