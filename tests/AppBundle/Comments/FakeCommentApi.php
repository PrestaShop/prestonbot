<?php

namespace Tests\AppBundle\Comments;

use AppBundle\Comments\CommentApiInterface;
use Github\Api\Issue\Comments as KnpCommentApi;
use PrestaShop\Github\Entity\PullRequest;
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

    public function send(PullRequest $pullRequest, string $comment)
    {
        return true;
    }

    public function sendWithTemplate(PullRequest $pullRequest, string $templateName, array $params)
    {
        $comment = $this->twig->render($templateName, $params);

        return $comment;
    }

    public function remove(int $commentId)
    {
        return true;
    }

    public function edit(int $commentId, string $comment)
    {
        return true;
    }

    public function editWithTemplate(int $commentId, string $templateName, array $params)
    {
        $comment = $this->twig->render($templateName, $params);

        return $comment;
    }
}
