<?php

namespace AppBundle\PullRequests;

use AppBundle\Comments\CommentApi;
use Lpdigital\Github\Entity\PullRequest;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Note to myself: too much logic in this Listener.
 */
class Listener
{
    private $commentApi;
    private $validator;

    public function __construct(CommentApi $commentApi, ValidatorInterface $validator)
    {
        $this->commentApi = $commentApi;
        $this->validator = $validator;
    }

    public function checkForDescription(PullRequest $pullRequest, $commitId)
    {
        $bodyParser = new BodyParser($pullRequest->getBody());

        $validationErrors = $this->validator->validate($bodyParser);
        if (count($validationErrors) > 0) {
            $this->commentApi->sendWithTemplate($pullRequest, 'markdown/pr_table_errors.md.twig', ['errors' => $validationErrors]);
        }
    }
}
