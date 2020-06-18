<?php

namespace AppBundle\Organizations;

use Github\Api\Organization;
use Github\Exception\RuntimeException;

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
    private $repositoryOwner;

    public function __construct(Organization $organizationApi, $repositoryOwner)
    {
        $this->organizationApi = $organizationApi;
        $this->repositoryOwner = $repositoryOwner;
    }

    /**
     * @return array
     */
    public function getTeams()
    {
        if (null === self::$teams) {
            $teams = $this->organizationApi
                ->teams()
                ->all($this->repositoryOwner)
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

    /**
     * Check if a user is a member of the organisation.
     *
     * @param string $userLogin
     *
     * @return bool
     */
    public function isMember(string $userLogin)
    {
        try {
            $this->organizationApi->members()->show($this->repositoryOwner, $userLogin);

            return true;
        } catch (RuntimeException $e) {
            return false;
        }
    }
}
