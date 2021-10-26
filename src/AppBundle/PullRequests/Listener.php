<?php

namespace AppBundle\PullRequests;

use AppBundle\Comments\CommentApiInterface;
use AppBundle\Commits\RepositoryInterface as CommitRepositoryInterface;
use AppBundle\Event\GitHubEvent;
use AppBundle\GithubDownloaderInterface;
use AppBundle\Organizations\RepositoryInterface as OrganizationRepository;
use AppBundle\PullRequests\RepositoryInterface as PullRequestRepositoryInterface;
use DateInterval;
use DateTime;
use PrestaShop\Github\Entity\PullRequest;
use PrestaShop\Github\Entity\User;
use PrestaShop\TranslationToolsBundle\Configuration;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\ChainExtractor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Listener
{
    const PRESTONBOT_NAME = 'prestonBot';
    const TABLE_ERROR = 'PR_TABLE_DESCRIPTION_ERROR';
    const COMMIT_ERROR = 'PR_COMMIT_NAME_ERROR';
    const WORDING_TAG = 'PR_WORDING';
    const FEEDBACK_COMMENT = 'PR_FEEDBACK_COMMENT';

    const TRANS_CONFIG_FILE = '.t9n.yml';

    const FEEDBACK_DELAY = 30; // days before asking feedback again

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
    /**
     * @var OrganizationRepository
     */
    private $organizationRepository;

    /**
     * @var GithubDownloaderInterface
     */
    private $githubDownloader;

    /**
     * @var ChainExtractor
     */
    private $chainExtractor;

    /**
     * @var string
     */
    private $cacheDir;

    public function __construct(
        CommentApiInterface $commentApi,
        CommitRepositoryInterface $commitRepository,
        ValidatorInterface $validator,
        PullRequestRepositoryInterface $repository,
        OrganizationRepository $organizationRepository,
        GithubDownloaderInterface $githubDownloader,
        ChainExtractor $chainExtractor,
        LoggerInterface $logger,
        string $cacheDir
    ) {
        $this->commentApi = $commentApi;
        $this->commitRepository = $commitRepository;
        $this->logger = $logger;
        $this->validator = $validator;
        $this->repository = $repository;
        $this->organizationRepository = $organizationRepository;
        $this->githubDownloader = $githubDownloader;
        $this->chainExtractor = $chainExtractor;
        $this->cacheDir = $cacheDir;
    }

    /**
     * @param PullRequest $pullRequest
     */
    public function checkForTableDescription(PullRequest $pullRequest)
    {
        $bodyParser = new BodyParser($pullRequest->getBody());

        $missingRelatedTicket = false;
        $validationErrors = $this->validator->validate($bodyParser);
        if ($bodyParser->isIssueRequired()) {
            $issueNeededValidationErrors = $this->validator->validate(
                $bodyParser,
                null,
                BodyParser::ISSUE_NEEDED_GROUP
            );
            if ($issueNeededValidationErrors->count() > 0) {
                $validationErrors->addAll($issueNeededValidationErrors);
                $missingRelatedTicket = true;
            }
        }
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
        if ($bodyParser->isIssueRequired()) {
            $bodyErrors->addAll($this->validator->validate(
                $bodyParser,
                null,
                BodyParser::ISSUE_NEEDED_GROUP
            ));
        }
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

    public function checkForMilestone(GitHubEvent $gitHubEvent)
    {
        $pullRequest = $gitHubEvent->getPullRequest();

        if (null === $pullRequest->getMilestone()) {
            $this->commentApi->sendWithTemplate(
                $pullRequest,
                'markdown/pr_missing_milestone.md.twig',
                []
            );

            return true;
        }

        return false;
    }

    /**
     * @param PullRequest $pullRequest
     *
     * @return bool
     */
    public function checkForNewTranslations(PullRequest $pullRequest): bool
    {
        $baseLineUrl = $pullRequest->getHtmlUrl().'/files#diff-%sR%s';
        $validated = [];
        $existingComment = $this->getExistingWordingComment($pullRequest);
        if (null !== $existingComment) {
            $validated = $this->getValidatedWordings($existingComment['body']);
        }

        set_time_limit(180);
        $base = $this->githubDownloader->downloadAndExtract($pullRequest, false);
        $head = $this->githubDownloader->downloadAndExtract($pullRequest);

        $catalogBase = new MessageCatalogue('en', []);
        $catalogHead = new MessageCatalogue('en', []);

        Configuration::fromYamlFile($this->cacheDir.'/'.$base.'/'.self::TRANS_CONFIG_FILE);
        $this->chainExtractor->extract($this->cacheDir.'/'.$base, $catalogBase);

        if (file_exists($this->cacheDir.'/'.$head.'/'.self::TRANS_CONFIG_FILE)) {
            Configuration::fromYamlFile($this->cacheDir.'/'.$head.'/'.self::TRANS_CONFIG_FILE);
        }
        $this->chainExtractor->extract($this->cacheDir.'/'.$head, $catalogHead);

        $newStrings = [];
        foreach ($catalogHead->all() as $domain => $strings) {
            foreach ($strings as $key => $string) {
                if (!isset($catalogBase->all()[$domain][$key])) {
                    if (!isset($newStrings[$domain])) {
                        $isNew = !isset($catalogBase->all()[$domain]);
                        $newStrings[$domain] = [
                            'validated' => isset($validated[$domain]) && $validated[$domain]['validated'],
                            'new' => $isNew,
                            'strings' => [],
                        ];
                    }
                    $meta = $catalogHead->getMetadata($key, $domain);
                    $filePath = substr($meta['file'], \strlen($this->cacheDir.'/'.$head.'/'));
                    $newStrings[$domain]['strings'][] = [
                        'string' => $key,
                        'validated' => isset($validated[$domain]) && \in_array($key, $validated[$domain]['strings'], true),
                        'link' => sprintf($baseLineUrl, hash('sha256', $filePath), $meta['line']),
                    ];
                }
            }
        }

        if (!empty($newStrings)) {
            $template = 'markdown/wordings.md.twig';
            $params = ['newStrings' => $newStrings];
            if (null === $existingComment) {
                $this->commentApi->sendWithTemplate($pullRequest, $template, $params);
            } else {
                $this->commentApi->editWithTemplate($existingComment['id'], $template, $params);
            }

            return true;
        }

        return false;
    }

    /**
     * @param PullRequest $pullRequest
     *
     * @return bool
     */
    public function askForFeedback(PullRequest $pullRequest)
    {
        $author = $pullRequest->getUser()->getLogin();

        if ($this->organizationRepository->isMember($author)) {
            return false;
        }

        $now = new DateTime();
        $since = $now->sub(DateInterval::createFromDateString(self::FEEDBACK_DELAY.' days'));
        $response = $this->repository->getMergedFromWithCommentsFrom($author, self::PRESTONBOT_NAME, $since);
        if (!empty($response['data']['search']['edges'])) {
            foreach ($response['data']['search']['edges'] as $pr) {
                foreach ($pr['node']['comments']['edges'] as $comment) {
                    if (self::PRESTONBOT_NAME === $comment['node']['author']['login']) {
                        if (false !== strpos($comment['node']['body'], self::FEEDBACK_COMMENT)) {
                            return false;
                        }
                    }
                }
            }
        }

        $this->commentApi->sendWithTemplate($pullRequest, 'markdown/pr_merged_feedback.md.twig', []);

        return true;
    }

    /**
     * Get existing new translations comment.
     *
     * @param PullRequest $pullRequest
     *
     * @return array|null
     */
    private function getExistingWordingComment(PullRequest $pullRequest): ?array
    {
        $existing = null;
        $comments = $this->repository->getCommentsByExpressionFrom(
            $pullRequest,
            self::WORDING_TAG,
            self::PRESTONBOT_NAME
        );

        if (\count($comments) > 0) {
            $existing = [
                'id' => $comments[0]->getId(),
                'body' => $comments[0]->getBody(),
            ];
        }

        return $existing;
    }

    /**
     * Return an array of already validated domains & translation strings.
     *
     * @param string $comment
     *
     * @return array
     */
    private function getValidatedWordings(string $comment): array
    {
        /*
         * $groupPattern is used for capturing individual Domain & the translation strings
         * related to it like :
         *
         *  - [ ] `Domain.Name`
         *      - [ ] [`new translation string`](http://github.com/...)
         *      - [x] [`new translation string`](http://github.com/...)
         *
         */
        $groupPattern = '/^- (?:\[(x| )\].*)?\s*`(.*)`((?:\s{4,}- \[.\] .*)+)/mi';

        /*
         * $wordingPattern is used for capturing validated individual translation strings like:
         *
         * - [x] [`new translation string`](http://github.com/...)
         */
        $wordingPattern = '/^\s+- \[x\] \[`(.*)`\]/mi';

        $validatedWordings = [];
        $matches = [];
        preg_match_all($groupPattern, $comment, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if ('x' === $match[1]) { // if domain is validated (- [x] `Domain.Name`)
                $validatedWordings[$match[2]] = [
                    'validated' => true,
                    'strings' => [],
                ];
            }
            $wordings = [];
            preg_match_all($wordingPattern, $match[3], $wordings, PREG_SET_ORDER);
            foreach ($wordings as $wording) {
                if (!isset($validatedWordings[$match[2]]['validated'])) {
                    $validatedWordings[$match[2]]['validated'] = false;
                }
                $validatedWordings[$match[2]]['strings'][] = $wording[1];
            }
        }

        return $validatedWordings;
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
