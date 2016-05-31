<?php

namespace AppBundle\PullRequests;

use Github\Api\Issue;
use Lpdigital\Github\Entity\PullRequest;
use AppBundle\PullRequests\BodyParser;
use Symfony\Component\Validator\ValidatorInterface;
use \Twig_Environment;

/**
 * Note to myself: too much logic in this Listener
 */
class Listener
{
    private $issueApi;
    private $repositoryUsername;
    private $repositoryName;
    private $validator;
    private $twig;
    
    public function __construct(Issue $issue, $repositoryUsername, $repositoryName, ValidatorInterface $validator, Twig_Environment $twig)
    {
        $this->issueApi = $issue;
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
        $this->validator = $validator;
        $this->twig = $twig;
    }
    
    public function checkForDescription(PullRequest $pullRequest, $commitId)
    {
        $bodyParser = new BodyParser($pullRequest->getBody());
        
        $validationErrors = $this->validator->validate($bodyParser);
        if(0 === count($validationErrors)) {
            $this->issueApi
                ->comments()
                ->create(
                    $this->repositoryUsername,
                    $this->repositoryName,
                    $pullRequest->getNumber(), [
                        'body' => 'created from describe botterland',
                    ]
                )
            ;
        }else {
            $bodyMessage = $this->twig->render('markdown/pr_table_errors.md.twig', ["errors" => $validationErrors]);
            dump($bodyMessage);
            $this->issueApi
                ->comments()
                ->create(
                    $this->repositoryUsername,
                    $this->repositoryName,
                    $pullRequest->getNumber(), [
                        'body' => $bodyMessage,
                    ]
                )
            ;
        }
        
    }
}
