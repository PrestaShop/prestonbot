<?php

namespace tests\AppBundle\Issues;

use PHPUnit\Framework\TestCase;
use AppBundle\Issues\Listener;
use AppBundle\Issues\Status;
use AppBundle\Issues\StatusApi;
use Psr\Log\NullLogger;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class IssueListenerTest extends TestCase
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

    public function testHandleWaitingForWordingEvent()
    {
        $this->statusApi->expects(static::once())
            ->method('addIssueLabel')
            ->with(1234, Status::WAITING_FOR_WORDING);

        $newStatus = $this->listener->handleWaitingForWordingEvent(1234);

        static::assertSame(Status::WAITING_FOR_WORDING, $newStatus);
    }
}
