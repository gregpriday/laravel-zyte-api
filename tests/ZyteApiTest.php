<?php

namespace GregPriday\ZyteApi\Tests;

use GregPriday\ZyteApi\ZyteApi;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class ZyteApiTest extends TestCase
{
    /** @test */
    public function it_initializes_with_default_parameters()
    {
        $zyteApi = new ZyteApi();
        $this->assertInstanceOf(ZyteApi::class, $zyteApi);
    }

    /** @test */
    public function it_extracts_http_body_from_url()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['httpResponseBody' => base64_encode('<html>Test</html>')])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $zyteApi = new ZyteApi('dummyKey', ZyteApi::API_ENDPOINT, $client);

        $response = $zyteApi->extractHttpBody('http://test.url');

        $this->assertEquals('<html>Test</html>', $response);
    }

    /** @test */
    public function it_handles_api_errors_gracefully()
    {
        $mock = new MockHandler([
            new Response(500, [], 'Internal Server Error'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $zyteApi = new ZyteApi('dummyKey', ZyteApi::API_ENDPOINT, $client);

        $response = $zyteApi->extractHttpBody('http://test.url');

        $this->assertStringContainsString('Error:', $response);
    }
}
