<?php

namespace AppBundle\PullRequests;

use AppBundle\Search\Repository as SearchRepository;
use Github\Api\Issue\Comments as KnpCommentsApi;
use Github\Exception\RuntimeException;
use PrestaShop\Github\Entity\Comment;
use PrestaShop\Github\Entity\PullRequest;

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
     * @var string
     */
    private $repositoryOwner;
    /**
     * @var string
     */
    private $repositoryName;

    public function __construct(
        SearchRepository $searchRepository,
        KnpCommentsApi $knpCommentsApi,
        $repositoryOwner,
        $repositoryName
        ) {
        $this->searchRepository = $searchRepository;
        $this->knpCommentsApi = $knpCommentsApi;
        $this->repositoryOwner = $repositoryOwner;
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
            $pullRequests[] = new PullRequest($pullRequest);
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
            $pullRequests[] = new PullRequest($pullRequest);
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
                    $this->repositoryOwner,
                    $this->repositoryName,
                    $pullRequest->getNumber()
                )
            ;
        } catch (RuntimeException $e) {
            $commentsApi = [];
        }

        $comments = [];
        foreach ($commentsApi as $comment) {
            $comments[] = new Comment($comment);
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
            if ($userLogin === $comment->getUser()->getLogin()) {
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

        if (\count($comments) > 0) {
            foreach ($comments as $comment) {
                $this->knpCommentsApi->remove(
                    $this->repositoryOwner,
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
