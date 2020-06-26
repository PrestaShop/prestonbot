<?php

namespace Tests\AppBundle\Organisations;

use AppBundle\Organizations\RepositoryInterface;

class FakeRepository implements RepositoryInterface
{
    private $members;

    public function __construct($members = [])
    {
        $this->members = $members;
    }

    public function getTeams()
    {
        // TODO: Implement getTeams() method.
    }

    public function getTeam(string $teamName)
    {
        // TODO: Implement getTeam() method.
    }

    public function getTeamMembers(string $teamName)
    {
        // TODO: Implement getTeamMembers() method.
    }

    public function isMember(string $userLogin)
    {
        return \in_array($userLogin, $this->members, true);
    }
}
