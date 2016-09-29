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
    const PR_TABLE_DESCRIPTION_ERROR = 'PR_TABLE_DESCRIPTION_ERROR';
    const PR_COMMIT_NAME_ERROR = 'PR_COMMIT_NAME_ERROR';

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
     * @todo: use Lpdigital\Entity\Commit when exists
     * @todo: if Pull request description is valid, proposal can be improved.
     */
    public function checkCommits(PullRequest $pullRequest)
    {
        $commits = $this->commitRepository->findAllByPullRequest($pullRequest);
        $commitErrors = [];

        foreach ($commits as $commit) {
            $commitLabel = $commit['commit']['message'];
            $commitParser = new CommitParser($commitLabel, $pullRequest);
            $commitErrors = $this->validator->validate($commitParser);

            if (count($commitErrors) > 0) {
                $commitErrors[] = $commitLabel;
            }
        }

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

        $validationErrors = $this->validator->validate($bodyParser);
        if (count($validationErrors) > 0) {
            $validationComments = $this->$this->repository
            ->getCommentsByExpressionFrom(
                $pullRequest,
                self::PR_TABLE_DESCRIPTION_ERROR,
                self::PRESTONBOT_NAME
            );

            if (count($validationComments) > 0) {
                $comment = $validationComments[0];
                $this->commentApi->remove($comment->getId());
            }
        }
    }

    public function removeCommitValidationComment(PullRequest $pullRequest)
    {
        //@todo
    }
}
