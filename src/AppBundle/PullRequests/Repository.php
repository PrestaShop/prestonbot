<?php

namespace AppBundle\PullRequests;

use Github\Api\Issue\Comments as CommentsApi;
use AppBundle\Search\Repository as SearchRepository;
use Lpdigital\Github\Entity\Comment;
use Lpdigital\Github\Entity\PullRequest;

/**
 * Get the pull requests according to some filters
 * As GitHub consider pull requests as specific issues
 * don't be surprised too much by the produced repository.
 */
class Repository
{
    const PRESTONBOT_NAME = 'prestonBot';

    private $searchRepository;
    private $commentsApi;

    private $repositoryUsername;
    private $repositoryName;

    public function __construct(
        SearchRepository $searchRepository,
        CommentsApi $commentsApi,
        $repositoryUsername,
        $repositoryName
        ) {
        $this->searchRepository = $searchRepository;
        $this->commentsApi = $commentsApi;
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
    }

    public function findAll($base = 'develop')
    {
        $pullRequests = [];
        $search = $this->searchRepository->getPullRequests(['base' => $base]);

        foreach ($search['items'] as $pullRequest) {
            $pullRequests[] = PullRequest::createFromData($pullRequest);
        }

        return $pullRequests;
    }

    public function findAllWithLabel($label, $base = 'develop')
    {
        $pullRequests = [];
        $search = $this->searchRepository->getPullRequests(
            [
                'label' => $this->parseLabel($label),
                'base' => $base,

            ]
        );

        foreach ($search['items'] as $pullRequest) {
            $pullRequests[] = PullRequest::createFromData($pullRequest);
        }

        return $pullRequests;
    }

    public function getComments(PullRequest $pullRequest)
    {
        $commentsApi = $this->commentsApi
            ->all(
                $this->repositoryUsername,
                $this->repositoryName,
                $pullRequest->getNumber()
            )
        ;

        $comments = [];
        foreach ($commentsApi as $comment) {
            $comments[] = Comment::createFromData($comment);
        }

        return $comments;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommentsFrom(PullRequest $pullRequest, $userLogin)
    {
        $comments = $this->getComments($pullRequest);
        $userComments = [];

        foreach ($comments as $comment) {
            if ($userLogin === $comment->getUserLogin()) {
                $userComments[] = $comment;
            }
        }

        return $userComments;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommentsByExpressionFrom(
        PullRequest $pullRequest,
        $expression,
        $userLogin
        ) {
        $userCommentsByExpression = [];
        $userComments = $this->getCommentsFrom($pullRequest, $userLogin);

        foreach ($userComments as $userComment) {
            if (strpos($userComment->getBody(), $expression) !== false) {
                $userCommentsByExpression[] = $userComment;
            }
        }

        return $userCommentsByExpression;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCommentsIfExists(PullRequest $pullRequest, $pattern)
    {
        $comments = $this->getCommentsByExpressionFrom(
            $pullRequest,
            $pattern,
            self::PRESTONBOT_NAME
        )
        ;

        if (count($comments) > 0) {
            foreach ($comments as $comment) {
                $this->remove($comment->getId());
            }
        }
    }

    private function parseLabel($label)
    {
        return '"'.$label.'"';
    }
}
