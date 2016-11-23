<?php

namespace AppBundle\Commits;

use Github\Exception\RuntimeException;
use AppBundle\Repositories\Repository as CommitsApi;
use Github\Api\PullRequest as PullRequestApi;
use Lpdigital\Github\Entity\User;
use Lpdigital\Github\Entity\Commit;
use Lpdigital\Github\Entity\PullRequest;

class Repository implements RepositoryInterface
{
    /**
     * @var CommitsApi
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
        CommitsApi $commitsApi,
        PullRequestApi $pullRequestApi,
        $repositoryUsername,
        $repositoryName
        ) {
        $this->commitsApi = $commitsApi;
        $this->pullRequestApi = $pullRequestApi;
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllByPullRequest(PullRequest $pullRequest)
    {
        try {
            $responseApi = $this->pullRequestApi->commits(
                $this->repositoryUsername,
                $this->repositoryName,
                $pullRequest->getNumber()
            );
        } catch (RuntimeException $e) {
            $responseApi = [];
        }

        return $this->buildCommits($responseApi);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllByUser(User $user)
    {
        try {
            $responseApi = $this->commitsApi
                ->getCommits()
                ->all(
                $this->repositoryUsername,
                $this->repositoryName,
                ['author' => $user->getLogin()]
            );
        } catch (RuntimeException $e) {
            $responseApi = [];
        }

        return $this->buildCommits($responseApi);
    }

    /**
     * @param array $responseApi
     *
     * @return \Lpdigital\Github\Entity\Commit[]|array
     */
    private function buildCommits(array $responseApi)
    {
        $commits = [];
        foreach ($responseApi as $commitApi) {
            $commits[] = Commit::createFromData($commitApi['commit']);
        }

        return $commits;
    }
}
