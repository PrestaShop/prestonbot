<?php

namespace AppBundle\Comments;

use Github\Api\Issue\Comments as KnpCommentApi;
use Lpdigital\Github\Entity\PullRequest;
use Twig_Environment;

/**
 * Responsible of comments publication on repository.
 */
class CommentApi implements CommentApiInterface
{
    /**
     * @var KnpCommentApi
     */
    private $knpCommentApi;

    /**
     * @var string
     */
    private $repositoryOwner;

    /**
     * @var string
     */
    private $repositoryName;

    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(KnpCommentApi $knpCommentApi, $repositoryOwner, $repositoryName, Twig_Environment $twig)
    {
        $this->knpCommentApi = $knpCommentApi;
        $this->repositoryOwner = $repositoryOwner;
        $this->repositoryName = $repositoryName;
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public function send(PullRequest $pullRequest, $comment)
    {
        $this->knpCommentApi
            ->create(
                $this->repositoryOwner,
                $this->repositoryName,
                $pullRequest->getNumber(), [
                    'body' => $comment,
                ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function sendWithTemplate(PullRequest $pullRequest, $templateName, $params)
    {
        $comment = $this->twig->render($templateName, $params);
        $this->knpCommentApi
            ->create(
                $this->repositoryOwner,
                $this->repositoryName,
                $pullRequest->getNumber(), [
                    'body' => $comment,
                ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($commentId)
    {
        $this->knpCommentApi
            ->remove(
                $this->repositoryOwner,
                $this->repositoryName,
                $commentId
            );
    }
}
