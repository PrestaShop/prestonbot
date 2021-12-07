<?php

namespace AppBundle\Organizations;

use Github\Api\Organization;

class Repository
{
    /**
     * @var array list of teams (won't change during a request)
     */
    protected static $teams;
    /**
     * @var Organization
     */
    private $organizationApi;

    /**
     * @var string
     */
    private $repositoryUsername;

    public function __construct(Organization $organizationApi, $repositoryUsername)
    {
        $this->organizationApi = $organizationApi;
        $this->repositoryUsername = $repositoryUsername;
    }

    /**
     * @return array
     */
    public function getTeams()
    {
        if (null === self::$teams) {
            $teams = $this->organizationApi
                ->teams()
                ->all($this->repositoryUsername)
            ;

            foreach ($teams as $team) {
                $teamName = $team['name'];
                self::$teams[$teamName] = $team;
            }
        }

        return self::$teams;
    }

    /**
     * @param string $teamName
     */
    public function getTeam(string $teamName)
    {
        $teams = $this->getTeams();

        return isset($teams[$teamName]) ? $teams[$teamName] : null;
    }

    /**
     * @param string $teamName
     *
     * @return \Guzzle\Http\EntityBodyInterface|mixed|string
     */
    public function getTeamMembers(string $teamName)
    {
        $teams = $this->getTeams();

        $teamId = isset($teams[$teamName]['id']) ? $teams[$teamName]['id'] : null;

        if (null !== $teamId) {
            return $this->organizationApi
                ->teams()
                ->members($teamId)
            ;
        }
    }
}
