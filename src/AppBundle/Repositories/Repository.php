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

    public function __construct(Repo $repositoryApi, $repositoryUsername, $repositoryName)
    {
        $this->repositoryApi = $repositoryApi;
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
    }

    public function getName()
    {
        return $this->repositoryName;
    }

    public function getActivity()
    {
        return $this->repositoryApi
            ->activity($this->repositoryUsername, $this->repositoryName)
        ;
    }

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

    public function getStars()
    {
        return $this->repositoryApi
            ->stargazers($this->repositoryUsername, $this->repositoryName)
        ;
    }

    public function getApi()
    {
        return $this->repositoryApi;
    }

    public function getMembers()
    {
        return $this->repositoryApi
            ->collaborators()
            ->all($this->repositoryUsername, $this->repositoryName)
        ;
    }
}
