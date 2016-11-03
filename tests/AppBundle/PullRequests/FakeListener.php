<?php

namespace tests\AppBundle\PullRequests;

use AppBundle\Comments\CommentApi;
use AppBundle\Commits\Repository as CommitRepository;
use AppBundle\PullRequests\BodyParser;
use AppBundle\PullRequests\CommitParser;
use Lpdigital\Github\Entity\PullRequest;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig_Environment;

/**
 * We don't create/update/delete comments intentionally.
 */
class FakeListener
{
    private $commentApi;
    private $commitRepository;
    private $validator;
    private $twig;

    public function __construct(
        CommentApi $commentApi,
        ValidatorInterface $validator,
        Twig_Environment $twig,
        CommitRepository $commitRepository
        ) {
        $this->commentApi = $commentApi;
        $this->commitRepository = $commitRepository;
        $this->validator = $validator;
        $this->twig = $twig;
    }

    public function checkForTableDescription(PullRequest $pullRequest)
    {
        $bodyParser = new BodyParser($pullRequest->getBody());

        $validationErrors = $this->validator->validate($bodyParser);
        if (count($validationErrors) > 0) {
            $this->twig->render('markdown/pr_table_errors.md.twig', ['errors' => $validationErrors]);

            return true;
        }
    }

    public function checkCommits(PullRequest $pullRequest)
    {
        $commits = $this->commitRepository->findAllByPullRequest($pullRequest);
        $validationErrors = [];

        foreach ($commits as $commit) {
            $commitLabel = $commit->getMessage();
            $commitParser = new CommitParser($commitLabel, $pullRequest);
            $commitErrors = $this->validator->validate($commitParser);

            if (count($commitErrors) > 0) {
                $validationErrors[] = $commitLabel;
            }
        }

        if (count($validationErrors) > 0) {
            $this->twig->render('markdown/pr_commit_name_nok.md.twig', ['commits' => $validationErrors]);

            return true;
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
                $commentIds[] = $comment->getId();
            }
        }

        return $commentIds;
    }
}
