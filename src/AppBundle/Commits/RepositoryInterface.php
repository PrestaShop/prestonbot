<?php

namespace AppBundle\Commits;

use Lpdigital\Github\Entity\User;
use Lpdigital\Github\Entity\PullRequest;

interface RepositoryInterface
{
    public function findAllByUser(User $user);

    public function findAllByPullRequest(PullRequest $pullRequest);
}
