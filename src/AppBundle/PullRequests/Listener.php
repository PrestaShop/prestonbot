<?php

namespace AppBundle\PullRequests;

use AppBundle\Comments\CommentApi;
use Lpdigital\Github\Entity\PullRequest;
use AppBundle\PullRequests\Repository as PullRequestRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Listener
{
    private $commentApi;
    private $validator;
    private $repository;

    const PRESTONBOT_NAME = 'prestonBot';

    public function __construct(
        CommentApi $commentApi,
        ValidatorInterface $validator,
        PullRequestRepository $repository
    ) {
        $this->commentApi = $commentApi;
        $this->validator = $validator;
        $this->repository = $repository;
    }

    public function handlePullRequestCreatedEvent(PullRequest $pullRequest, $commitId)
    {
        $bodyParser = new BodyParser($pullRequest->getBody());

        $validationErrors = $this->validator->validate($bodyParser);
        if (count($validationErrors) > 0) {
            $this->commentApi->sendWithTemplate(
                $pullRequest,
                'markdown/pr_table_errors.md.twig',
                ['errors' => $validationErrors]
            );
        }
    }

    public function handlePullRequestEditedEvent(PullRequest $pullRequest)
    {
        $prestonComments = $this->repository
            ->getCommentsFrom($pullRequest, self::PRESTONBOT_NAME)
        ;

        if (count($prestonComments) > 0) {
            $validationComment = $prestonComments[0];
            $this->commentApi->remove($validationComment->getId());
        }
    }
}
