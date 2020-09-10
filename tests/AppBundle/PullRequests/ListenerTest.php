<?php

namespace Tests\AppBundle\PullRequests;

use AppBundle\Comments\CommentApi;
use AppBundle\Commits\Repository as CommitRepository;
use AppBundle\PullRequests\BodyParser;
use AppBundle\PullRequests\Listener;
use AppBundle\PullRequests\Repository;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use PrestaShop\Github\Entity\Comment;
use PrestaShop\Github\Entity\PullRequest;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\ChainExtractor;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\PhpExtractor;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;
use Tests\AppBundle\FakeGithubDownloader;

class ListenerTest extends TestCase
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var Listener
     */
    private $listener;

    /**
     * @var CommentApi
     */
    private $commentApiMock;

    /**
     * @var Repository
     */
    private $repositoryMock;

    public function setUp()
    {
        $this->validator = (new ValidatorBuilder())
            ->enableAnnotationMapping()
            ->getValidator();

        $this->commentApiMock = $this->createMock(CommentApi::class);
        $this->commentApiMock->method('sendWithTemplate')
            ->willReturn(true)
        ;

        $this->repositoryMock = $this->createMock(Repository::class);

        $commitRepository = $this->createMock(CommitRepository::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $logger = $this->createMock(Logger::class);

        $githubDownloader = new FakeGithubDownloader();
        $chainExtractor = new ChainExtractor();
        $chainExtractor->addExtractor('php', new PhpExtractor());

        $this->listener = new Listener(
            $this->commentApiMock,
            $commitRepository,
            $validator,
            $this->repositoryMock,
            $githubDownloader,
            $chainExtractor,
            $logger,
            __DIR__.'/../../Resources/repos'
        );
    }

    /**
     * @dataProvider getDescriptionTests
     *
     * @param $descriptionFilename
     * @param $expected
     */
    public function testDescriptions($descriptionFilename, $expected)
    {
        $body = file_get_contents(__DIR__.'/../../Resources/PullRequestBody/'.$descriptionFilename);
        $bodyParser = new BodyParser($body);

        $validations = $this->validator->validate($bodyParser);
        if (!$bodyParser->isTestCategory()) {
            $validations->addAll($this->validator->validate($bodyParser, null, BodyParser::NOT_TEST_GROUP));
        }
        $this->assertSame(\count($expected), \count($validations));
        foreach ($validations as $validation) {
            $this->assertContains($validation->getPropertyPath(), $expected);
        }
    }

    /**
     * @dataProvider getWordingTests
     *
     * @param string $payloadFile
     * @param array  $newWordings
     */
    public function testWordings(string $payloadFile, array $newStrings, bool $validated = false)
    {
        $pullRequest = new PullRequest(json_decode(file_get_contents($payloadFile), true)['pull_request']);

        if ($validated) {
            $validatedComments = $this->getValidatedComment();
            $this->commentApiMock->expects(empty($newStrings) ? $this->never() : $this->once())
                ->method('editWithTemplate')
                ->with($validatedComments[0]->getId(), 'markdown/wordings.md.twig', ['newStrings' => $newStrings])
            ;
        } else {
            $validatedComments = [];
            $this->commentApiMock->expects(empty($newStrings) ? $this->never() : $this->once())
                ->method('sendWithTemplate')
                ->with($pullRequest, 'markdown/wordings.md.twig', ['newStrings' => $newStrings])
            ;
        }

        $this->repositoryMock->method('getCommentsByExpressionFrom')
            ->willReturn($validatedComments)
        ;

        $newTranslations = $this->listener->checkForNewTranslations($pullRequest);
        $this->assertTrue($newTranslations === !empty($newStrings));
    }

    public function getDescriptionTests()
    {
        return [
            'Valid description' => [
                'bug_fix.txt',
                [],
            ],
            'Missing description' => [
                'missing_description.txt',
                ['description'],
            ],
            'Invalid type' => [
                'invalid_type.txt',
                ['type'],
            ],
            'Invalid category' => [
                'invalid_category.txt',
                ['category'],
            ],
            'No related ticked' => [
                'no_related_ticket.txt',
                ['relatedTicket'],
            ],
            'No related ticked TE' => [
                'no_related_ticket_TE.txt',
                [],
            ],
        ];
    }

    public function getWordingTests()
    {
        return [
            'New Wording not validated' => [
                __DIR__.'/../webhook_examples/pull_request_opened_wording.json',
                [
                    'New.Admin.Global' => [
                        'validated' => false,
                        'new' => true,
                        'strings' => [
                            [
                                'string' => 'Customer',
                                'validated' => false,
                                'link' => 'https://github.com/PrestaShop/PrestaShop/pull/6833/files#diff-ec3df2e862bbf3a25db2795a5eebad72R322',
                            ],
                            [
                                'string' => 'Total',
                                'validated' => false,
                                'link' => 'https://github.com/PrestaShop/PrestaShop/pull/6833/files#diff-ec3df2e862bbf3a25db2795a5eebad72R323',
                            ],
                        ],
                    ],
                    'Admin.Global' => [
                        'validated' => false,
                        'new' => false,
                        'strings' => [
                            [
                                'string' => 'Payment',
                                'validated' => false,
                                'link' => 'https://github.com/PrestaShop/PrestaShop/pull/6833/files#diff-ec3df2e862bbf3a25db2795a5eebad72R324',
                            ],
                            [
                                'string' => 'Status',
                                'validated' => false,
                                'link' => 'https://github.com/PrestaShop/PrestaShop/pull/6833/files#diff-ec3df2e862bbf3a25db2795a5eebad72R325',
                            ],
                            [
                                'string' => 'Date',
                                'validated' => false,
                                'link' => 'https://github.com/PrestaShop/PrestaShop/pull/6833/files#diff-ec3df2e862bbf3a25db2795a5eebad72R326',
                            ],
                        ],
                    ],
                ],
            ],
            'New Wording validated' => [
                __DIR__.'/../webhook_examples/pull_request_opened_wording.json',
                [
                    'New.Admin.Global' => [
                        'validated' => true,
                        'new' => true,
                        'strings' => [
                            [
                                'string' => 'Customer',
                                'validated' => false,
                                'link' => 'https://github.com/PrestaShop/PrestaShop/pull/6833/files#diff-ec3df2e862bbf3a25db2795a5eebad72R322',
                            ],
                            [
                                'string' => 'Total',
                                'validated' => true,
                                'link' => 'https://github.com/PrestaShop/PrestaShop/pull/6833/files#diff-ec3df2e862bbf3a25db2795a5eebad72R323',
                            ],
                        ],
                    ],
                    'Admin.Global' => [
                        'validated' => false,
                        'new' => false,
                        'strings' => [
                            [
                                'string' => 'Payment',
                                'validated' => false,
                                'link' => 'https://github.com/PrestaShop/PrestaShop/pull/6833/files#diff-ec3df2e862bbf3a25db2795a5eebad72R324',
                            ],
                            [
                                'string' => 'Status',
                                'validated' => true,
                                'link' => 'https://github.com/PrestaShop/PrestaShop/pull/6833/files#diff-ec3df2e862bbf3a25db2795a5eebad72R325',
                            ],
                            [
                                'string' => 'Date',
                                'validated' => false,
                                'link' => 'https://github.com/PrestaShop/PrestaShop/pull/6833/files#diff-ec3df2e862bbf3a25db2795a5eebad72R326',
                            ],
                        ],
                    ],
                ],
                true,
            ],
        ];
    }

    private function getValidatedComment(): array
    {
        return [new Comment([
            'id' => 1,
            'node_id' => 'MDExOlB1bGxSZXF1ZXN0OTEzNzU2MzQ=',
            'body' => file_get_contents(__DIR__.'/../../Resources/Comments/validated_wordings.txt'),
            'url' => '',
            'html_url' => '',
            'user' => [
                'login' => '',
                'id' => '',
                'node_id' => '',
                'avatar_url' => '',
                'gravatar_id' => '',
                'url' => '',
                'html_url' => '',
                'followers_url' => '',
                'following_url' => '',
                'gists_url' => '',
                'starred_url' => '',
                'subscriptions_url' => '',
                'organizations_url' => '',
                'repos_url' => '',
                'events_url' => '',
                'received_events_url' => '',
                'type' => '',
                'site_admin' => '',
            ],
            'created_at' => '',
            'updated_at' => '',
            'author_association' => '',
        ])];
    }
}
