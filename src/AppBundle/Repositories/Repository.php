<?php

namespace AppBundle\Repositories;

use Github\Api\Repo;

class Repository
{
    /**
     * @var Repository
     */
    private $repositoryApi;

    /**
     * @var string
     */
    private $repositoryUsername;

    /**
     * @var string
     */
    private $repositoryName;

    public function __construct(Repo $repositoryApi, string $repositoryUsername, string $repositoryName)
    {
        $this->repositoryApi = $repositoryApi;
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->repositoryName;
    }

    /**
     * @return array
     */
    public function getActivity()
    {
        return $this->repositoryApi
            ->activity($this->repositoryUsername, $this->repositoryName)
        ;
    }

    /**
     * @return array
     */
    public function getStatistics()
    {
        return $this->repositoryApi
            ->statistics($this->repositoryUsername, $this->repositoryName)
        ;
    }

    /**
     * Get the top contributors.
     */
    public function getTopContributors()
    {
        return $this->repositoryApi
            ->contributors($this->repositoryUsername, $this->repositoryName, true)
        ;
    }

    /**
     * @return \Github\Api\Repository\Stargazers
     */
    public function getStars()
    {
        return $this->repositoryApi
            ->stargazers($this->repositoryUsername, $this->repositoryName)
        ;
    }

    /**
     * @return Repository|Repo
     */
    public function getApi()
    {
        return $this->repositoryApi;
    }

    /**
     * @return \Github\Api\Repository\Commits
     */
    public function getCommits()
    {
        return $this->repositoryApi->commits();
    }

    /**
     * @return array
     */
    public function getMembers()
    {
        return $this->repositoryApi
            ->collaborators()
            ->all($this->repositoryUsername, $this->repositoryName)
        ;
    }
}
