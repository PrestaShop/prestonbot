<?php

namespace AppBundle\PullRequests;

use AppBundle\Comments\GitHubCommentApi;
use Lpdigital\Github\Entity\PullRequest;
use AppBundle\PullRequests\BodyParser;
use Symfony\Component\Validator\ValidatorInterface;
use \Twig_Environment;

/**
 * Note to myself: too much logic in this Listener
 */
class Listener
{
    private $commentApi;
    private $validator;
    private $twig;
    
    public function __construct(GitHubCommentApi $commentApi, ValidatorInterface $validator, Twig_Environment $twig)
    {
        $this->commentApi = $commentApi;
        $this->validator = $validator;
        $this->twig = $twig;
    }
    
    public function checkForDescription(PullRequest $pullRequest, $commitId)
    {
        $bodyParser = new BodyParser($pullRequest->getBody());
        
        $validationErrors = $this->validator->validate($bodyParser);
        if(count($validationErrors) > 0) {
            $bodyMessage = $this->twig->render('markdown/pr_table_errors.md.twig', ["errors" => $validationErrors]);
            $this->commentApi->send($pullRequest, $bodyMessage);
        }
    }
}
