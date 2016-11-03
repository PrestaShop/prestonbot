<?php

namespace AppBundle\PullRequests;

use Lpdigital\Github\Entity\PullRequest;

interface RepositoryInterface
{
    public function findAll($base);

    public function findAllWithLabel($label, $base);

    public function getComments(PullRequest $pullRequest);

    /**
     * Return Comments of selected user if any.
     *
     * @param PullRequest \Lpdigital\Github\Entity\PullRequest
     * @param string login from Entity User of Comment entry
     *
     * @return array collection of user's comments
     */
    public function getCommentsFrom(PullRequest $pullRequest, $userLogin);

    /**
     * Return Comments of selected user if any, filtered by expression.
     *
     * @param PullRequest \Lpdigital\Github\Entity\PullRequest
     * @param string login from Entity User of Comment entry
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
     */
    public function removeCommentsIfExists(PullRequest $pullRequest, $pattern);
}
