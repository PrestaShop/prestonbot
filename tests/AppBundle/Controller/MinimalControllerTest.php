<?php

namespace tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Functional tests for pages of PrestonBot website.
 */
class MinimalControllerTest extends WebTestCase
{
    /**
     * @var Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    protected function setUp()
    {
        $this->client = static::createClient();
    }

    public function testHomepageOk()
    {
        $this->client->request('HEAD', '/');
        $response = $this->client->getResponse();

        static::assertSame(200, $response->getStatusCode());
    }
}
