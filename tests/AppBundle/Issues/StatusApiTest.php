<?php

namespace Tests\AppBundle\Issues\GitHub;

use PHPUnit\Framework\TestCase;
use AppBundle\Issues\CachedLabelsApi;
use AppBundle\Issues\Status;
use AppBundle\Issues\StatusApi;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StatusApiTest extends TestCase
{
    const USER_NAME = 'weaverryan';

    const REPO_NAME = 'carson';

    /**
     * @var CachedLabelsApi|\PHPUnit_Framework_MockObject_MockObject
     */
    private $labelsApi;

    /**
     * @var StatusApi
     */
    private $api;

    protected function setUp()
    {
        $this->labelsApi = $this->getMockBuilder('AppBundle\Issues\CachedLabelsApi')
            ->disableOriginalConstructor()
            ->getMock();
        $this->api = new StatusApi($this->labelsApi, self::USER_NAME, self::REPO_NAME);
    }

    public function testAddIssueLabel()
    {
        $this->labelsApi->expects($this->once())
            ->method('getIssueLabels')
            ->with(1234)
            ->willReturn(['Bug', 'Status: Needs Review']);

        $this->labelsApi->expects($this->once())
            ->method('addIssueLabel')
            ->with(1234, 'Code reviewed');

        $this->api->addIssueLabel(1234, Status::CODE_REVIEWED);
    }

    public function testGetNeedsReviewUrl()
    {
        $this->assertSame(
            'https://github.com/weaverryan/carson/labels/waiting%20for%20code%20review',
            $this->api->getNeedsReviewUrl()
        );
    }
}
