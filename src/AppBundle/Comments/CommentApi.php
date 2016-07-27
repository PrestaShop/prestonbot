<?php

namespace AppBundle\Comments;

use Github\Api\Issue\Comments as KnpCommentApi;
use Lpdigital\Github\Entity\PullRequest;
use Twig_Environment;

/**
 * Responsible of comments publication on repository.
 */
class CommentApi
{
    /**
     * @var KnpCommentApi
     */
    private $knpCommentApi;

    /**
     * @var string
     */
    private $repositoryUsername;

    /**
     * @var string
     */
    private $repositoryName;

    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(KnpCommentApi $knpCommentApi, $repositoryUsername, $repositoryName, Twig_Environment $twig)
    {
        $this->knpCommentApi = $knpCommentApi;
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
        $this->twig = $twig;
    }

    public function send(PullRequest $pullRequest, $comment)
    {
        $this->knpCommentApi
            ->create(
                $this->repositoryUsername,
                $this->repositoryName,
                $pullRequest->getNumber(), [
                    'body' => $comment,
                ]
            )
        ;
    }

    public function sendWithTemplate(PullRequest $pullRequest, $templateName, $params)
    {
        $comment = $this->twig->render($templateName, $params);
        $this->knpCommentApi
            ->create(
                $this->repositoryUsername,
                $this->repositoryName,
                $pullRequest->getNumber(), [
                    'body' => $comment,
                ]
            )
        ;
    }

    public function remove($commentId)
    {
        $this->knpCommentApi
            ->remove(
                $this->repositoryUsername,
                $this->repositoryName,
                $commentId
            )
        ;
    }
}
