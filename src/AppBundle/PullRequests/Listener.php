<?php

namespace AppBundle\PullRequests;

use Github\Api\Issue;
use Lpdigital\Github\Entity\PullRequest;
use AppBundle\PullRequests\BodyParser;
use Symfony\Component\Validator\ValidatorInterface;

class Listener
{
    private $issueApi;
    private $repositoryUsername;
    private $repositoryName;
    private $validator;
    
    public function __construct(Issue $issue, $repositoryUsername, $repositoryName, ValidatorInterface $validator)
    {
        $this->issueApi = $issue;
        $this->repositoryUsername = $repositoryUsername;
        $this->repositoryName = $repositoryName;
        $this->validator = $validator;
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
            $errorMessage = 'This pull request seems to be incomplete or malformed: '. PHP_EOL;
            foreach($validationErrors as $error) {
                // @todo should be in an editable template
                $errorMessage .= '* '.$error->getMessage(). PHP_EOL;
            }
            $this->issueApi
                ->comments()
                ->create(
                    $this->repositoryUsername,
                    $this->repositoryName,
                    $pullRequest->getNumber(), [
                        'body' => $errorMessage,
                    ]
                )
            ;
        }
        
    }
}
