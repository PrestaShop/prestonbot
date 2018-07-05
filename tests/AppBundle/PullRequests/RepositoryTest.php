<?php

namespace tests\AppBundle\PullRequests;

use AppBundle\PullRequests\Repository;
use PHPUnit\Framework\TestCase;

/**
 * @author Mickaël Andrieu <andrieu.travail@gmail.com>
 */
class RepositoryTest extends TestCase
{
    const REPOSITORY_USERNAME = 'loveOSS';
    const REPOSITORY_NAME = 'test';

    private $commentsApiMock;
    private $pullRequestMock;
    private $repository;

    public function setUp()
    {
        $searchMock = $this->createMock('AppBundle\Search\Repository');
        $this->commentsApiMock = $this->createMock('Github\Api\Issue\Comments');

        $searchMock->method('getPullRequests')
            ->will($this->returnCallback([$this, 'generateExpectedArray']))
        ;

        $this->commentsApiMock->method('all')
            ->will($this->returnCallback([$this, 'exportCommentsJson']))
        ;

        $this->commentsApiMock->method('remove')
            ->willReturn(true)
        ;

        $this->pullRequestMock = $this->createMock('Lpdigital\Github\Entity\PullRequest');

        $this->pullRequestMock
            ->method('getNumber')
            ->willReturn('123')
        ;

        $this->repository = new Repository(
            $searchMock,
            $this->commentsApiMock,
            self::REPOSITORY_USERNAME,
            self::REPOSITORY_NAME
        );
    }

    public function tearDown()
    {
        $this->repository = null;
    }

    public function generateExpectedArray($filters)
    {
        if (isset($filters['label'])) {
            $filename = 'search_repository_one_label.json';
        } else {
            $filename = 'search_repository_all.json';
        }

        $fileContent = file_get_contents(__DIR__.'/../webhook_examples/'.$filename);

        return [
            'count' => 0,
            'incomplete_results' => false,
            'items' => json_decode($fileContent, true),
        ];
    }

    public function exportCommentsJson()
    {
        $fileContent = file_get_contents(__DIR__.'/../webhook_examples/pull_request_comments.json');

        return json_decode($fileContent, true);
    }

    public function testFindAll()
    {
        $pullRequests = $this->repository->findAll();
        $this->minimalTests($pullRequests);
    }

    public function testFindAllWithLabel()
    {
        $pullRequests = $this->repository->findAllWithLabel('waiting for code review');
        $this->minimalTests($pullRequests);
    }

    public function testGetComments()
    {
        $comments = $this->repository->getComments($this->pullRequestMock);
        $this->assertInternalType('array', $comments);
        $firstComment = $comments[0];
        $this->assertInstanceOf('Lpdigital\Github\Entity\Comment', $firstComment);
        $this->assertSame('mickaelandrieu', $firstComment->getUserLogin());
    }

    public function testGetCommentsByExpressionFromMatch()
    {
        $user = 'Shudrum';
        $comment = 'POC added just to not merge to quickly.';

        $comments = $this->repository->getCommentsByExpressionFrom(
            $this->pullRequestMock,
            $comment,
            $user
        );

        $this->assertInternalType('array', $comments);
        $firstComment = $comments[0];
        $this->assertInstanceOf('Lpdigital\Github\Entity\Comment', $firstComment);
        $this->assertSame('Shudrum', $firstComment->getUserLogin());
    }

    public function testGetCommentsByExpressionFromNotMatch()
    {
        $user = 'Shudrum';
        $comment = 'Hello world';

        $comments = $this->repository->getCommentsByExpressionFrom(
            $this->pullRequestMock,
            $comment,
            $user
        );

        $this->assertInternalType('array', $comments);
        $this->assertEmpty($comments);
    }

    public function testRemoveCommentsIfExists()
    {
        $this->commentsApiMock->expects($this->once())
            ->method('remove')
        ;

        $this->repository->removeCommentsIfExists(
            $this->pullRequestMock,
            'POC added just to not merge to quickly.',
            'Shudrum'
        );
    }

    private function minimalTests($pullRequests)
    {
        $this->assertInternalType('array', $pullRequests);
        $this->assertInstanceOf('Lpdigital\Github\Entity\PullRequest', $pullRequests[0]);
    }
}
