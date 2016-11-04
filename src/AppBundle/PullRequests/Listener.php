<?php

namespace AppBundle\PullRequests;

use AppBundle\Comments\CommentApiInterface;
use AppBundle\Commits\RepositoryInterface as CommitRepositoryInterface;
use Lpdigital\Github\Entity\PullRequest;
use AppBundle\PullRequests\RepositoryInterface as PullRequestRepositoryInterface;
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
        CommentApiInterface $commentApi,
        CommitRepositoryInterface $commitRepository,
        ValidatorInterface $validator,
        PullRequestRepositoryInterface $repository
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
            $this->repository->removeCommentsIfExists(
                $pullRequest,
                self::TABLE_ERROR,
                self::PRESTONBOT_NAME
            );

            return true;
        }

        return false;
    }

    public function removeCommitValidationComment(PullRequest $pullRequest)
    {
        if (0 === count($this->getErrorsFromCommits($pullRequest))) {
            $this->repository->removeCommentsIfExists(
                $pullRequest,
                self::COMMIT_ERROR,
                self::PRESTONBOT_NAME
            );

            return true;
        }

        return false;
    }

    public function welcomePeople(PullRequest $pullRequest, User $sender, $branch)
    {
        $userCommits = $this->commitRepository->findAllByBranchAndUserLogin($branch, $sender);

        if (0 === count($userCommits)) {
            $this->commentApi->sendWithTemplate(
                $pullRequest,
                'markdown/welcome.md.twig',
                ['username' => $pullRequest->getUser()->getLogin()]
            );
        }
    }

    /**
     * Wrap the validation of commits.
     * 
     * @return array error messages if any.
     */
    private function getErrorsFromCommits(PullRequest $pullRequest)
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
}
