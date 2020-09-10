<?php

namespace AppBundle\Commits;

use PrestaShop\Github\Entity\Commit;
use PrestaShop\Github\Entity\PullRequest;
use PrestaShop\Github\Entity\User;

interface RepositoryInterface
{
    /**
     * @param User $user
     *
     * @return Commit[]|array
     */
    public function findAllByUser(User $user);

    /**
     * @param PullRequest $pullRequest
     *
     * @return Commit[]|array
     */
    public function findAllByPullRequest(PullRequest $pullRequest);
}
