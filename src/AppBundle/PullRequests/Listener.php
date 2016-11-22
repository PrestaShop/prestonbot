<?php

namespace AppBundle\PullRequests;

use AppBundle\PullRequests\RepositoryInterface as PullRequestRepositoryInterface;
use AppBundle\Commits\RepositoryInterface as CommitRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use AppBundle\Comments\CommentApiInterface;
use Lpdigital\Github\Entity\PullRequest;
use Lpdigital\Github\Entity\User;
use Psr\Log\LoggerInterface;

class Listener
{
    private $commentApi;
    private $commitRepository;
    private $logger;
    private $validator;
    private $repository;

    const PRESTONBOT_NAME = 'prestonBot';
    const TABLE_ERROR = 'PR_TABLE_DESCRIPTION_ERROR';
    const COMMIT_ERROR = 'PR_COMMIT_NAME_ERROR';

    public function __construct(
        CommentApiInterface $commentApi,
        CommitRepositoryInterface $commitRepository,
        ValidatorInterface $validator,
        PullRequestRepositoryInterface $repository,
        LoggerInterface $logger
    ) {
        $this->commentApi = $commentApi;
        $this->commitRepository = $commitRepository;
        $this->logger = $logger;
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

            $this->logger->info(sprintf('[Invalid Table] Pull request n° %s', $pullRequest->getNumber()));
        }
    }

    public function checkCommits(PullRequest $pullRequest)
    {
        $commitErrors = $this->getErrorsFromCommits($pullRequest);

        if (count($commitErrors) > 0) {
            $this->commentApi->sendWithTemplate(
                $pullRequest,
                'markdown/pr_commit_name_nok.md.twig',
                ['commits' => $commitErrors]
            );

            $commitsLabels = implode(',', array_map(function ($label) {
                return '`'.$label.'`';
            }, $commitErrors));

            $this->logger->info(sprintf(
                '[Invalid Commits]Pull request n° %s for commits %s',
                $pullRequest->getNumber(),
                $commitsLabels
            ));

            return true;
        }

        return false;
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

            $this->logger->info(sprintf(
                '[Valid Table] Pull request (n° %s) table is now valid.',
                $pullRequest->getNumber()
            ));

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

            $this->logger->info(sprintf(
                '[Valid Commits] Pull request (n° %s) commits are now valid.',
                $pullRequest->getNumber()
            ));

            return true;
        }

        return false;
    }

    public function welcomePeople(PullRequest $pullRequest, User $sender)
    {
        $userCommits = $this->commitRepository->findAllByUser($sender);

        if (0 === count($userCommits)) {
            $this->commentApi->sendWithTemplate(
                $pullRequest,
                'markdown/welcome.md.twig',
                ['username' => $sender->getLogin()]
            );

            $this->logger->info(sprintf(
                '[Contributor] `%s` was welcomed on Pull request n° %s',
                $pullRequest->getUser()->getLogin(),
                $pullRequest->getNumber()
            ));
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
