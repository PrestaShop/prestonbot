<?php

namespace Tests\AppBundle\PullRequests;

use AppBundle\PullRequests\RepositoryInterface;
use AppBundle\Search\Repository as SearchRepository;
use Github\Api\Issue\Comments as KnpCommentsApi;
use Lpdigital\Github\Entity\Comment;
use Lpdigital\Github\Entity\PullRequest;

/**
 * Get the pull requests according to some filters
 * As GitHub consider pull requests as specific issues
 * don't be surprised too much by the produced repository.
 */
class FakeRepository implements RepositoryInterface
{
    public function findAll(string $base = 'develop')
    {
        return [];
    }

    public function findAllWithLabel(string $label, string $base = 'develop')
    {
        return [];
    }

    public function getComments(PullRequest $pullRequest)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getCommentsFrom(PullRequest $pullRequest, $userLogin)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getCommentsByExpressionFrom(
        PullRequest $pullRequest,
        $expression,
        $userLogin
    ) {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function removeCommentsIfExists(PullRequest $pullRequest, $pattern, $userLogin)
    {
        return true;
    }
}
