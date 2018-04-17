<?php

namespace AppBundle\PullRequests;

use AppBundle\Search\Repository as SearchRepository;
use Github\Api\Issue\Comments as KnpCommentsApi;
use Github\Exception\RuntimeException;
use Lpdigital\Github\Entity\Comment;
use Lpdigital\Github\Entity\PullRequest;

/**
 * Get the pull requests according to some filters
 * As GitHub consider pull requests as specific issues
 * don't be surprised too much by the produced repository.
 */
class Repository implements RepositoryInterface
{
    /**
     * @var SearchRepository
     */
    private $searchRepository;
    /**
     * @var KnpCommentsApi
     */
    private $knpCommentsApi;

    /**
     * @var
     */
    private $repositoryUsername;
    /**
     * @var
     */
    private $repositoryName;

    public function __construct(
        SearchRepository $searchRepository,
        KnpCommentsApi $knpCommentsApi,
        $repositoryUsername,
        $repositoryName
        ) {
        $this->searchRepository = $searchRepository;
        $this->knpCommentsApi = $knpCommentsApi;
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(string $base = 'develop')
    {
        $pullRequests = [];
        $search = $this->searchRepository->getPullRequests(['base' => $base]);

        foreach ($search['items'] as $pullRequest) {
            $pullRequests[] = PullRequest::createFromData($pullRequest);
        }

        return $pullRequests;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllWithLabel(string $label, string $base = 'develop')
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

    /**
     * {@inheritdoc}
     */
    public function getComments(PullRequest $pullRequest)
    {
        try {
            $commentsApi = $this->knpCommentsApi
                ->all(
                    $this->repositoryUsername,
                    $this->repositoryName,
                    $pullRequest->getNumber()
                )
            ;
        } catch (RuntimeException $e) {
            $commentsApi = [];
        }

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
            if (false !== strpos($userComment->getBody(), $expression)) {
                $userCommentsByExpression[] = $userComment;
            }
        }

        return $userCommentsByExpression;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCommentsIfExists(PullRequest $pullRequest, $pattern, $userLogin)
    {
        $comments = $this->getCommentsByExpressionFrom(
            $pullRequest,
            $pattern,
            $userLogin
        )
        ;

        if (count($comments) > 0) {
            foreach ($comments as $comment) {
                $this->knpCommentsApi->remove(
                    $this->repositoryUsername,
                    $this->repositoryName,
                    $comment->getId()
                );
            }

            return true;
        }

        return false;
    }

    /**
     * @param $label
     *
     * @return string
     */
    private function parseLabel(string $label)
    {
        return '"'.$label.'"';
    }
}
