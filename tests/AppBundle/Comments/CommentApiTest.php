<?php

namespace Tests\AppBundle\Comments;

use AppBundle\Comments\CommentApi;
use PHPUnit\Framework\TestCase;
use PrestaShop\Github\Entity\PullRequest;

class CommentApiTest extends TestCase
{
    const USER_NAME = 'weaverryan';
    const REPO_NAME = 'carson';
    const FAKE_COMMENT_ID = 123;

    private $knpCommentApi;
    private $commentApi;
    private $pullRequest;
    private $twig;

    public function setUp(): void
    {
        $this->twig = $this->createMock('Twig\Environment');

        $this->twig
            ->method('render')
            ->with('tpl/foo.html.twig', ['bar' => 'baz'])
            ->willReturn('<h1> Hello Baz !</h1>')
        ;

        $this->knpCommentApi = $this->createMock('Github\Api\Issue\Comments');
        $this->commentApi = new CommentApi(
            $this->knpCommentApi,
            self::USER_NAME,
            self::REPO_NAME, $this->twig
        );
        $this->pullRequest = $this->createMock(PullRequest::class);

        $this->pullRequest
            ->method('getNumber')
            ->willReturn(42)
        ;
    }

    public function testSend()
    {
        $bodyComment = ['body' => 'foo'];

        $this->knpCommentApi
            ->expects($this->once())
            ->method('create')
            ->with(
                self::USER_NAME,
                self::REPO_NAME,
                42,
                $bodyComment
            )
        ;

        $this->twig->expects($this->never())->method('render');

        $this->commentApi->send($this->pullRequest, 'foo');
    }

    public function testSendWithTemplate()
    {
        $this->knpCommentApi
            ->expects($this->once())
            ->method('create')
            ->with(
                self::USER_NAME,
                self::REPO_NAME,
                42,
                ['body' => '<h1> Hello Baz !</h1>']
            )
        ;

        $this->twig->expects($this->once())->method('render');

        $this->commentApi->sendWithTemplate(
            $this->pullRequest,
            'tpl/foo.html.twig',
            ['bar' => 'baz']
        );
    }

    public function testRemove()
    {
        $this->knpCommentApi
            ->expects($this->once())
            ->method('remove')
            ->with(
                self::USER_NAME,
                self::REPO_NAME,
                self::FAKE_COMMENT_ID
            )
        ;

        $this->commentApi->remove(self::FAKE_COMMENT_ID);
    }
}
