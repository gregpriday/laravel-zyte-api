<?php

namespace GregPriday\ZyteApi\Tests;

use Dotenv\Dotenv;
use GregPriday\ZyteApi\Proxy\ZyteClient;
use GregPriday\ZyteApi\ZyteApi;

class ZyteApiTest extends TestCase
{
    protected function setUp(): void
    {
        Dotenv::createImmutable(__DIR__.'/../')->load();
        parent::setUp();
    }

    /** @test */
    public function test_fetch_http_response_body()
    {
        $api = app(ZyteApi::class);
        $response = $api->extract('https://searchsocket.com');

        $this->assertNotEmpty($response['httpResponseBody']);
        $this->assertEquals(200, $response['statusCode']);
    }

    public function test_fetch_http_using_proxy()
    {
        $client = app(ZyteClient::class);
        $response = $client->get('https://searchsocket.com');

        $httpResponseBody = $response->getBody()->getContents();
        $this->assertNotEmpty($httpResponseBody);
    }
}
