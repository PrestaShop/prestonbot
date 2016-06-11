<?php

namespace AppBundle\Comments;

use Github\Api\Issue;
use Lpdigital\Github\Entity\PullRequest;
use Twig_Environment;

/**
 * Responsible of comments publication on repository.
 */
class CommentApi
{
    /**
     * @var Issue
     */
    private $issue;

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

    public function __construct(Issue $issue, $repositoryUsername, $repositoryName, Twig_Environment $twig)
    {
        $this->issue = $issue;
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
        $this->twig = $twig;
    }

    public function send(PullRequest $pullRequest, $comment)
    {
        $this->issue
            ->comments()
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
        $this->issue
            ->comments()
            ->create(
                $this->repositoryUsername,
                $this->repositoryName,
                $pullRequest->getNumber(), [
                    'body' => $comment,
                ]
            )
        ;
    }
}
