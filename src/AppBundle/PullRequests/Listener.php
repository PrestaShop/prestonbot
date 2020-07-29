<?php

namespace AppBundle\PullRequests;

use AppBundle\Comments\CommentApiInterface;
use AppBundle\Commits\RepositoryInterface as CommitRepositoryInterface;
use AppBundle\PullRequests\RepositoryInterface as PullRequestRepositoryInterface;
use Lpdigital\Github\Entity\PullRequest;
use Lpdigital\Github\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Listener
{
    const PRESTONBOT_NAME = 'prestonBot';
    const TABLE_ERROR = 'PR_TABLE_DESCRIPTION_ERROR';
    const COMMIT_ERROR = 'PR_COMMIT_NAME_ERROR';
    /**
     * @var CommentApiInterface
     */
    private $commentApi;
    /**
     * @var CommitRepositoryInterface
     */
    private $commitRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var RepositoryInterface
     */
    private $repository;

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

    /**
     * @param PullRequest $pullRequest
     */
    public function checkForTableDescription(PullRequest $pullRequest)
    {
        $bodyParser = new BodyParser($pullRequest->getBody());

        $validationErrors = $this->validator->validate($bodyParser);
        $missingRelatedTicket = empty($bodyParser->getRelatedTicket());
        if (\count($validationErrors) > 0) {
            $this->commentApi->sendWithTemplate(
                $pullRequest,
                'markdown/pr_table_errors.md.twig',
                ['errors' => $validationErrors, 'missingRelatedTicket' => $missingRelatedTicket]
            );

            $this->logger->info(sprintf('[Invalid Table] Pull request n° %s', $pullRequest->getNumber()));
        }
    }

    /**
     * @param PullRequest $pullRequest
     *
     * @return bool
     */
    public function removePullRequestValidationComment(PullRequest $pullRequest)
    {
        $bodyParser = new BodyParser($pullRequest->getBody());

        $bodyErrors = $this->validator->validate($bodyParser);
        if (0 === \count($bodyErrors)) {
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

    /**
     * @param PullRequest $pullRequest
     *
     * @return bool
     */
    public function removeCommitValidationComment(PullRequest $pullRequest)
    {
        if (0 === \count($this->getErrorsFromCommits($pullRequest))) {
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

    /**
     * @param PullRequest $pullRequest
     * @param User        $sender
     *
     * @return bool
     */
    public function welcomePeople(PullRequest $pullRequest, User $sender)
    {
        $userCommits = $this->commitRepository->findAllByUser($sender);

        if (0 !== \count($userCommits)) {
            return false;
        }

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

        return true;
    }

    /**
     * Wrap the validation of commits.
     *
     * @return array error messages if any
     */
    private function getErrorsFromCommits(PullRequest $pullRequest)
    {
        $commits = $this->commitRepository->findAllByPullRequest($pullRequest);
        $commitsErrors = [];

        foreach ($commits as $commit) {
            $commitLabel = $commit->getMessage();
            $commitParser = new CommitParser($commitLabel, $pullRequest);
            $validationErrors = $this->validator->validate($commitParser);

            if (\count($validationErrors) > 0) {
                $commitsErrors[] = $commitLabel;
            }
        }

        return $commitsErrors;
    }
}
