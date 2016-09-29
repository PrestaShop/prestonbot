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
            $bodyMessage = $this->twig->render('markdown/pr_table_errors.md.twig', ['errors' => $validationErrors]);

            return true;
        }
    }

    public function checkCommits(PullRequest $pullRequest)
    {
        $commits = $this->commitRepository->findAllByPullRequest($pullRequest);
        $validationErrors = [];

        foreach ($commits as $commit) {
            $commitLabel = $commit['commit']['message'];
            $commitParser = new CommitParser($commitLabel, $pullRequest);
            $commitErrors = $this->validator->validate($commitParser);

            if (count($commitErrors) > 0) {
                $validationErrors[] = $commitLabel;
            }
        }

        if (count($validationErrors) > 0) {
            $bodyMessage = $this->twig->render('markdown/pr_commit_name_nok.md.twig', ['commits' => $validationErrors]);

            dump($bodyMessage);

            return true;
        }
    }

    public function removePullRequestValidationComment(PullRequest $pullRequest)
    {
        $prestonComments = $this->repository
            ->getCommentsFrom($pullRequest, self::PRESTONBOT_NAME)
        ;

        if (count($prestonComments) > 0) {
            $validationComment = $prestonComments[0];

            return true;
        }

        return false;
    }
}
