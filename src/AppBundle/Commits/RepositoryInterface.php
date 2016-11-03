<?php

namespace AppBundle\Commits;

use Lpdigital\Github\Entity\PullRequest;

interface RepositoryInterface
{
    public function findAllByPullRequest(PullRequest $pullRequest);
}
