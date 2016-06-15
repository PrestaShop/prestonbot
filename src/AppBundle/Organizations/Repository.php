<?php

namespace AppBundle\Organizations;

use Github\Api\Organization;

class Repository
{
    /**
     * @var Organization
     */
    private $organizationApi;

    /**
     * @var string
     */
    private $repositoryUsername;
    
    /**
     * @var array list of teams (won't change during a request)
     */
    static protected $teams; 

    public function __construct(Organization $organizationApi, $repositoryUsername)
    {
        $this->organizationApi = $organizationApi;
        $this->repositoryUsername = $repositoryUsername;
    }
    
    public function getTeams()
    {
        if (null === self::$teams) {
            $teams = $this->organizationApi
                ->teams()
                ->all($this->repositoryUsername)
            ;
            
            foreach($teams as $team) {
                $teamName = $team['name'];
                self::$teams[$teamName] = $team;
            }
        }
        
        return self::$teams;
    }
    
    public function getTeam($teamName)
    {
        $teams = $this->getTeams();
        
        return isset($teams[$teamName]) ? $teams[$teamName] : null;
    }
    
    public function getTeamMembers($teamName)
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