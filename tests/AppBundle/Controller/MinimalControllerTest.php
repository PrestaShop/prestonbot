<?php

namespace tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @tdodo: use data provider
 */
class MinimalControllerTest extends WebTestCase
{
    private $client;

    protected function setUp()
    {
        $this->client = $this->createClient();
        $this->client->insulate();
    }

    public function testHomepageOk()
    {
        $this->client->request('GET', '/');
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPullRequestDashboardOk()
    {
        $this->client->request('GET', '/dashboard/pull_requests');
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testTeamsDashboardOk()
    {
        $this->client->request('GET', '/dashboard/teams');
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }
}
