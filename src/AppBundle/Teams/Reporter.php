<?php

namespace AppBundle\Teams;

use AppBundle\Organizations\Repository;

/**
 * Returns useful information about organization teams.
 */
class Reporter
{
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return array
     */
    public function reportTeamsAndMembers()
    {
        $report = [];

        $teams = $this->repository->getTeams();

        foreach ($teams as &$team) {
            $team['members'] = $this->repository->getTeamMembers($team['name']);
            $report[] = $team;
        }

        return $report;
    }
}
