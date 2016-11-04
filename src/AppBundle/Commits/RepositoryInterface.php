<?php

namespace AppBundle\Commits;

use Lpdigital\Github\Entity\PullRequest;

interface RepositoryInterface
{
    public function findAllByBranchAndUserLogin($branch, $userLogin);

    public function findAllByPullRequest(PullRequest $pullRequest);
}
