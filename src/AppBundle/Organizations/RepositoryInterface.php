<?php

namespace AppBundle\Organizations;

interface RepositoryInterface
{
    /**
     * @return array
     */
    public function getTeams();

    /**
     * @param string $teamName
     */
    public function getTeam(string $teamName);

    /**
     * @param string $teamName
     *
     * @return \Guzzle\Http\EntityBodyInterface|mixed|string
     */
    public function getTeamMembers(string $teamName);

    /**
     * Check if a user is a member of the organisation.
     *
     * @param string $userLogin
     *
     * @return bool
     */
    public function isMember(string $userLogin);
}
