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
    }

    public function testHomepageOk()
    {
        $this->client->request('HEAD', '/');
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }
}
