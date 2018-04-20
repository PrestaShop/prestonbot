<?php

namespace tests\AppBundle\Issues;

use AppBundle\Issues\Listener;
use AppBundle\Issues\Status;
use AppBundle\Issues\StatusApi;
use Psr\Log\NullLogger;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class IssueListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StatusApi|\PHPUnit_Framework_MockObject_MockObject
     */
    private $statusApi;

    /**
     * @var IssueListener
     */
    private $listener;

    protected function setUp()
    {
        $this->statusApi = $this->createMock('AppBundle\Issues\StatusApi');
        $this->listener = new Listener($this->statusApi, new NullLogger());
    }

    /**
     * @dataProvider getCommentsForStatusChange
     *
     * @param mixed $comment
     * @param mixed $expectedStatus
     */
    public function testHandleCommentAddedEvent($comment, $expectedStatus)
    {
        if (null !== $expectedStatus) {
            $this->statusApi->expects($this->once())
                ->method('addIssueLabel')
                ->with(1234, $expectedStatus);
        }

        $newStatus = $this->listener->handleCommentAddedEvent(1234, $comment);

        $this->assertSame($expectedStatus, $newStatus);
    }

    public function getCommentsForStatusChange()
    {
        $tests = [];
        $tests[] = [
            'Have a great day!',
            null,
        ];
        $tests[] = [
            "Status: 'PM approved'",
            Status::PM_APPROVED,
        ];
        $tests[] = [
            "Status: 'QA approved'",
            Status::QA_APPROVED,
        ];
        // basic tests for status change
        $tests[] = [
            'Status: needs review',
            Status::NEEDS_REVIEW,
        ];
        $tests[] = [
            'Status: Code reviewed',
            Status::CODE_REVIEWED,
        ];

        // accept quotes
        $tests[] = [
            'Status: "Code reviewed"',
            Status::CODE_REVIEWED,
        ];
        $tests[] = [
            "Status: 'Code reviewed'",
            Status::CODE_REVIEWED,
        ];
        // play with different formatting
        $tests[] = [
            'STATUS: CODE REVIEWED',
            Status::CODE_REVIEWED,
        ];
        $tests[] = [
            '**Status**: code reviewed',
            Status::CODE_REVIEWED,
        ];
        $tests[] = [
            '**Status:** code reviewed',
            Status::CODE_REVIEWED,
        ];
        $tests[] = [
            '**Status: code reviewed**',
            Status::CODE_REVIEWED,
        ];
        $tests[] = [
            '**Status: code reviewed!**',
            Status::CODE_REVIEWED,
        ];
        $tests[] = [
            '**Status: code reviewed**.',
            Status::CODE_REVIEWED,
        ];
        $tests[] = [
            'Status:code reviewed',
            Status::CODE_REVIEWED,
        ];
        $tests[] = [
            'Status:    code reviewed',
            Status::CODE_REVIEWED,
        ];

        // reject missing colon
        $tests[] = [
            'Status code reviewed',
            null,
        ];

        // multiple matches - use the last one
        $tests[] = [
            "Status: needs review \r\n that is what the issue *was* marked as.\r\n Status: Code reviewed",
            Status::CODE_REVIEWED,
        ];
        // "needs review" does not come directly after status: , so there is no status change
        $tests[] = [
            'Here is my status: I\'m really happy! I realize this needs review, but I\'m, having too much fun Googling cats!',
            null,
        ];

        // reject if the status is not on a line of its own
        // use case: someone posts instructions about how to change a status
        // in a comment
        $tests[] = [
            'You should include e.g. the line `Status: needs review` in your comment',
            null,
        ];
        $tests[] = [
            'Before the ticket was in state "Status: Code reviewed", but then the status was changed',
            null,
        ];

        return $tests;
    }

    public function testHandlePullRequestCreatedEvent()
    {
        $this->statusApi->expects($this->once())
            ->method('addIssueLabel')
            ->with(1234, Status::NEEDS_REVIEW);

        $newStatus = $this->listener->handlePullRequestCreatedEvent(1234);

        $this->assertSame(Status::NEEDS_REVIEW, $newStatus);
    }
}
