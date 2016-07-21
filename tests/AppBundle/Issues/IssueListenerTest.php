<?php

namespace tests\AppBundle\Issues;

use AppBundle\Issues\Listener;
use AppBundle\Issues\StatusApi;
use AppBundle\Issues\Status;

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
        $this->listener = new Listener($this->statusApi);
    }

    /**
     * @dataProvider getCommentsForStatusChange
     */
    public function testHandleCommentAddedEvent($comment, $expectedStatus)
    {
        if (null !== $expectedStatus) {
            $this->statusApi->expects($this->once())
                ->method('setIssueStatus')
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
            'Status: reviewed',
            Status::REVIEWED,
        ];

        // accept quotes
        $tests[] = [
            'Status: "reviewed"',
            Status::REVIEWED,
        ];
        $tests[] = [
            "Status: 'reviewed'",
            Status::REVIEWED,
        ];
        // play with different formatting
        $tests[] = [
            'STATUS: REVIEWED',
            Status::REVIEWED,
        ];
        $tests[] = [
            '**Status**: reviewed',
            Status::REVIEWED,
        ];
        $tests[] = [
            '**Status:** reviewed',
            Status::REVIEWED,
        ];
        $tests[] = [
            '**Status: reviewed**',
            Status::REVIEWED,
        ];
        $tests[] = [
            '**Status: reviewed!**',
            Status::REVIEWED,
        ];
        $tests[] = [
            '**Status: reviewed**.',
            Status::REVIEWED,
        ];
        $tests[] = [
            'Status:reviewed',
            Status::REVIEWED,
        ];
        $tests[] = [
            'Status:     reviewed',
            Status::REVIEWED,
        ];

        // reject missing colon
        $tests[] = [
            'Status reviewed',
            null,
        ];

        // multiple matches - use the last one
        $tests[] = [
            "Status: needs review \r\n that is what the issue *was* marked as.\r\n Status: reviewed",
            Status::REVIEWED,
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
            'Before the ticket was in state "Status: reviewed", but then the status was changed',
            null,
        ];

        return $tests;
    }

    public function testHandlePullRequestCreatedEvent()
    {
        $this->statusApi->expects($this->once())
            ->method('setIssueStatus')
            ->with(1234, Status::NEEDS_REVIEW);

        $newStatus = $this->listener->handlePullRequestCreatedEvent(1234);

        $this->assertSame(Status::NEEDS_REVIEW, $newStatus);
    }

    public function testHandleLabelAddedEvent()
    {
        $this->statusApi->expects($this->once())
            ->method('getIssueStatus')
            ->with(1234)
            ->willReturn(null);

        $this->statusApi->expects($this->once())
            ->method('setIssueStatus')
            ->with(1234, Status::NEEDS_REVIEW);

        $newStatus = $this->listener->handleLabelAddedEvent(1234, 'bug');

        $this->assertSame(Status::NEEDS_REVIEW, $newStatus);
    }

    public function testHandleLabelAddedEventIgnoresBugCase()
    {
        $this->statusApi->expects($this->once())
            ->method('getIssueStatus')
            ->with(1234)
            ->willReturn(null);

        $this->statusApi->expects($this->once())
            ->method('setIssueStatus')
            ->with(1234, Status::NEEDS_REVIEW);

        $newStatus = $this->listener->handleLabelAddedEvent(1234, 'BUG');

        $this->assertSame(Status::NEEDS_REVIEW, $newStatus);
    }

    public function testHandleLabelAddedEventIgnoresNonBugs()
    {
        $this->statusApi->expects($this->never())
            ->method('getIssueStatus');

        $this->statusApi->expects($this->never())
            ->method('setIssueStatus');

        $newStatus = $this->listener->handleLabelAddedEvent(1234, 'feature');

        $this->assertNull($newStatus);
    }

    public function testHandleLabelAddedEventIgnoresIfExistingStatus()
    {
        $this->statusApi->expects($this->once())
            ->method('getIssueStatus')
            ->with(1234)
            ->willReturn(Status::NEEDS_REVIEW);

        $this->statusApi->expects($this->never())
            ->method('setIssueStatus');

        $newStatus = $this->listener->handleLabelAddedEvent(1234, 'bug');

        $this->assertNull($newStatus);
    }
}
