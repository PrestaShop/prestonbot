<?php

namespace tests\AppBundle\Comments;

use Github\Api\Issue\Comments as KnpCommentApi;
use AppBundle\Comments\CommentApiInterface;
use Lpdigital\Github\Entity\PullRequest;
use Twig_Environment;

/**
 * Responsible of comments publication on repository.
 */
class FakeCommentApi implements CommentApiInterface
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
        return true;
    }

    public function sendWithTemplate(PullRequest $pullRequest, $templateName, $params)
    {
        $comment = $this->twig->render($templateName, $params);

        return $comment;
    }

    public function remove($commentId)
    {
        return true;
    }
}
