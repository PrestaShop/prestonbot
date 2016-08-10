<?php

namespace tests\AppBundle\PullRequests;

use AppBundle\Comments\CommentApi;
use AppBundle\PullRequests\BodyParser;
use Lpdigital\Github\Entity\PullRequest;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig_Environment;

/**
 * We don't create/update/delete comments intentionally.
 */
class FakeListener
{
    private $commentApi;
    private $validator;
    private $twig;

    public function __construct(CommentApi $commentApi, ValidatorInterface $validator, Twig_Environment $twig)
    {
        $this->commentApi = $commentApi;
        $this->validator = $validator;
        $this->twig = $twig;
    }

    public function handlePullRequestCreatedEvent(PullRequest $pullRequest, $commitId)
    {
        $bodyParser = new BodyParser($pullRequest->getBody());

        $validationErrors = $this->validator->validate($bodyParser);
        if (count($validationErrors) > 0) {
            $bodyMessage = $this->twig->render('markdown/pr_table_errors.md.twig', ['errors' => $validationErrors]);

            return true;
        }
    }

    public function handlePullRequestEditedEvent(PullRequest $pullRequest)
    {
        $prestonComments = $this->repository
            ->getCommentsFrom($pullRequest, self::PRESTONBOT_NAME)
        ;

        if (count($prestonComments) > 0) {
            $validationComment = $prestonComments[0];

            return true;
        }

        return false;
    }
}
