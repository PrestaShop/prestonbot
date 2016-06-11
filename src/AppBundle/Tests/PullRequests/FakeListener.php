<?php

namespace AppBundle\Tests\PullRequests;

use AppBundle\Comments\CommentApi;
use AppBundle\PullRequests\BodyParser;
use Lpdigital\Github\Entity\PullRequest;
use Symfony\Component\Validator\ValidatorInterface;
use Twig_Environment;

/**
 * Note to myself: in integrations tests, don't send comments!
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

    public function checkForDescription(PullRequest $pullRequest, $commitId)
    {
        $bodyParser = new BodyParser($pullRequest->getBody());

        $validationErrors = $this->validator->validate($bodyParser);
        if (count($validationErrors) > 0) {
            $bodyMessage = $this->twig->render('markdown/pr_table_errors.md.twig', ['errors' => $validationErrors]);

            return true;
        }
    }
}
