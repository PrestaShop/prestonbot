<?php

namespace Tests\AppBundle\PullRequests;

use AppBundle\Comments\CommentApi;
use AppBundle\Commits\Repository as CommitRepository;
use AppBundle\PullRequests\BodyParser;
use AppBundle\PullRequests\Listener;
use AppBundle\PullRequests\Repository;
use Lpdigital\Github\Entity\Comment;
use Lpdigital\Github\Entity\PullRequest;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
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
                            ['string' => 'Customer', 'validated' => false],
                            ['string' => 'Total', 'validated' => false],
                        ],
                    ],
                    'Admin.Global' => [
                        'validated' => false,
                        'new' => false,
                        'strings' => [
                            ['string' => 'Payment', 'validated' => false],
                            ['string' => 'Status', 'validated' => false],
                            ['string' => 'Date', 'validated' => false],
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
                            ['string' => 'Customer', 'validated' => false],
                            ['string' => 'Total', 'validated' => true],
                        ],
                    ],
                    'Admin.Global' => [
                        'validated' => false,
                        'new' => false,
                        'strings' => [
                            ['string' => 'Payment', 'validated' => false],
                            ['string' => 'Status', 'validated' => true],
                            ['string' => 'Date', 'validated' => false],
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
            'body' => file_get_contents(__DIR__.'/../../Resources/Comments/validated_wordings.txt'),
            'url' => '',
            'html_url' => '',
            'user' => [
                'name' => '',
            ],
            'created_at' => '',
            'updated_at' => '',
        ])];
    }
}
