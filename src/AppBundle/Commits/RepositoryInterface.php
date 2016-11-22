<?php

namespace AppBundle\Commits;

use Lpdigital\Github\Entity\PullRequest;

interface RepositoryInterface
{
    public function findAllByUserLogin($userLogin);

    public function findAllByPullRequest(PullRequest $pullRequest);
}
