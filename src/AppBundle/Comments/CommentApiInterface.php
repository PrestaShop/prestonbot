<?php

namespace AppBundle\Comments;

use Lpdigital\Github\Entity\PullRequest;

interface CommentApiInterface
{
    /**
     * @param PullRequest $pullRequest
     * @param $comment
     *
     * @return mixed
     */
    public function send(PullRequest $pullRequest, $comment);

    /**
     * @param PullRequest $pullRequest
     * @param $templateName
     * @param $params
     *
     * @return mixed
     */
    public function sendWithTemplate(PullRequest $pullRequest, $templateName, $params);

    /**
     * @param $commentId
     *
     * @return mixed
     */
    public function remove($commentId);
}
