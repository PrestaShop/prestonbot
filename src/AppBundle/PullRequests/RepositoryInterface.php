<?php

namespace AppBundle\PullRequests;

use PrestaShop\Github\Entity\PullRequest;
use DateTime;

interface RepositoryInterface
{
    public function findAll(string $base);

    public function findAllWithLabel(string $label, string $base);

    public function getComments(PullRequest $pullRequest);

    /**
     * Return Comments of selected user if any.
     *
     * @param PullRequest \Lpdigital\Github\Entity\PullRequest
     * @param string login from Entity User of Comment entry
     * @param mixed $userLogin
     *
     * @return array collection of user's comments
     */
    public function getCommentsFrom(PullRequest $pullRequest, $userLogin);

    /**
     * Return Comments of selected user if any, filtered by expression.
     *
     * @param PullRequest \Lpdigital\Github\Entity\PullRequest
     * @param string login from Entity User of Comment entry
     * @param mixed $expression
     * @param mixed $userLogin
     *
     * @return array collection of user's filtered comments
     */
    public function getCommentsByExpressionFrom(
        PullRequest $pullRequest,
        $expression,
        $userLogin
    );

    /**
     * Wraps the remove of existing PrestonBot comments.
     *
     * @param PullRequest the pull request
     * @param $pattern expression to filter comments in CommentApi
     * @param mixed $userLogin
     */
    public function removeCommentsIfExists(PullRequest $pullRequest, $pattern, $userLogin);

    /**
     * Returns all merged pull requests of a specific user commented by another specific user.
     *
     * @param string        $mergedFrom  userLogin of the author of the PR
     * @param string        $commentedBy userLogin of the author of the comment we're looking for
     * @param DateTime|null $since       since what date should we get the PRs
     *
     * @return array
     */
    public function getMergedFromWithCommentsFrom(string $mergedFrom, string $commentedBy, ?DateTime $since = null);
}
