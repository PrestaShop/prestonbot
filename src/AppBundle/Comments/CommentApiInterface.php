<?php

namespace AppBundle\Comments;

use Lpdigital\Github\Entity\PullRequest;

interface CommentApiInterface
{
    public function send(PullRequest $pullRequest, $comment);

    public function sendWithTemplate(PullRequest $pullRequest, $templateName, $params);

    public function remove($commentId);
}
