<?php

namespace Tests\AppBundle\PullRequests;

use AppBundle\PullRequests\RepositoryInterface;
use PrestaShop\Github\Entity\PullRequest;
use DateTime;

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

    /**
     * {@inheritdoc}
     */
    public function getMergedFromWithCommentsFrom(string $mergedFrom, string $commentedBy, ?DateTime $since = null)
    {
        $query = 'repo:loveOSS/test is:pr is:merged author:'.$mergedFrom.' commenter:'.$commentedBy;
        $parts = explode(' ', $query);
        $filename = preg_replace('/[\/:]/', '', implode('_', $parts));
        $file = __DIR__.'/../webhook_examples/'.$filename.'.json';
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true);
        }

        return [];
    }
}
