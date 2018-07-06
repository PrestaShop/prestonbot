<?php

namespace AppBundle\Comments;

use Lpdigital\Github\Entity\PullRequest;

interface CommentApiInterface
{
    /**
     * @param PullRequest $pullRequest
     * @param string      $comment
     *
     * @return mixed
     */
    public function send(PullRequest $pullRequest, string $comment);

    /**
     * @param PullRequest $pullRequest
     * @param string      $templateName
     * @param array       $params
     *
     * @return mixed
     */
    public function sendWithTemplate(PullRequest $pullRequest, string $templateName, array $params);

    /**
     * @param int $commentId
     *
     * @return mixed
     */
    public function remove(int $commentId);
}
