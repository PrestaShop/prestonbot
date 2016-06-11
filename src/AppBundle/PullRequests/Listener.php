<?php

namespace AppBundle\PullRequests;

use AppBundle\Comments\GitHubCommentApi;
use Lpdigital\Github\Entity\PullRequest;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Note to myself: too much logic in this Listener.
 */
class Listener
{
    private $commentApi;
    private $validator;

    public function __construct(GitHubCommentApi $commentApi, ValidatorInterface $validator)
    {
        $this->commentApi = $commentApi;
        $this->validator = $validator;
    }

    public function checkForDescription(PullRequest $pullRequest, $commitId)
    {
        $bodyParser = new BodyParser($pullRequest->getBody());

        $validationErrors = $this->validator->validate($bodyParser);
        if (count($validationErrors) > 0) {
            $this->commentApi->sendTemplate($pullRequest, 'markdown/pr_table_errors.md.twig', ['errors' => $validationErrors]);
        }
    }
}
