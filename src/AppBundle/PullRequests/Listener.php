<?php

namespace AppBundle\PullRequests;

use AppBundle\Comments\CommentApi;
use AppBundle\Commits\Repository as CommitRepository;
use Lpdigital\Github\Entity\PullRequest;
use AppBundle\PullRequests\Repository as PullRequestRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Listener
{
    private $commentApi;
    private $commitRepository;
    private $validator;
    private $repository;

    const PRESTONBOT_NAME = 'prestonBot';
    const TABLE_ERROR = 'PR_TABLE_DESCRIPTION_ERROR';
    const COMMIT_ERROR = 'PR_COMMIT_NAME_ERROR';

    public function __construct(
        CommentApi $commentApi,
        CommitRepository $commitRepository,
        ValidatorInterface $validator,
        PullRequestRepository $repository
    ) {
        $this->commentApi = $commentApi;
        $this->commitRepository = $commitRepository;
        $this->validator = $validator;
        $this->repository = $repository;
    }

    public function checkForTableDescription(PullRequest $pullRequest)
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

    /**
     * @todo: if Pull request description is valid, proposal can be improved.
     */
    public function checkCommits(PullRequest $pullRequest)
    {
        $commitErrors = $this->getErrorsFromCommits($pullRequest);

        if (count($commitErrors) > 0) {
            $this->commentApi->sendWithTemplate(
                $pullRequest,
                'markdown/pr_commit_name_nok.md.twig',
                ['commits' => $commitErrors]
            );
        }
    }

    public function removePullRequestValidationComment(PullRequest $pullRequest)
    {
        $bodyParser = new BodyParser($pullRequest->getBody());

        $bodyErrors = $this->validator->validate($bodyParser);
        if (0 === count($bodyErrors)) {
            $this->removeCommentsIfExists($pullRequest, self::TABLE_ERROR);
        }
    }

    public function removeCommitValidationComment(PullRequest $pullRequest)
    {
        if (0 === $this->getErrorsFromCommits($pullRequest)) {
            $this->removeCommentsIfExists($pullRequest, self::COMMIT_ERROR);
        }
    }

    /**
     * Wrap the validation of commits.
     * 
     * @return array error messages if any.
     */
    public function getErrorsFromCommits(PullRequest $pullRequest)
    {
        $commits = $this->commitRepository->findAllByPullRequest($pullRequest);
        $commitsErrors = [];

        foreach ($commits as $commit) {
            $commitLabel = $commit->getMessage();
            $commitParser = new CommitParser($commitLabel, $pullRequest);
            $validationErrors = $this->validator->validate($commitParser);

            if (count($validationErrors) > 0) {
                $commitsErrors[] = $commitLabel;
            }
        }

        return $commitsErrors;
    }

     /**
      * Wraps the remove of existing PrestonBot comments.
      * 
      * @param PullRequest the pull request
      * @param $pattern expression to filter comments in CommentApi
      */
     public function removeCommentsIfExists(PullRequest $pullRequest, $pattern)
     {
         $comments = $this->repository
            ->getCommentsByExpressionFrom(
                $pullRequest,
                $pattern,
                self::PRESTONBOT_NAME
            )
        ;

         if (count($comments) > 0) {
             foreach ($comments as $comment) {
                 $this->commentApi->remove($comment->getId());
             }
         }
     }
}
