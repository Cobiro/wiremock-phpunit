<?php

declare(strict_types=1);

namespace Tests\Integration;

use GuzzleHttp\Client;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Tests\Trait\RequestTrait;

final class DummyClientTest extends TestCase
{
    use RequestTrait;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client([
            'base_uri' => 'http://wiremock:8080',
        ]);
    }

    protected function tearDown(): void
    {
        try {
            parent::tearDown();
        } catch (AssertionFailedError $exception) {
            self::assertStringContainsString(
                sprintf(
                    'WireMock verification failed for test %s',
                    self::class . ':' . $this->getName()
                ),
                $exception->getMessage()
            );
        }
    }

    public function testItVerifiesRequest(): void
    {
        $expectedBody = ['someKey' => 'someValue'];

        $this->mockTestRequest((string) json_encode($expectedBody));

        $result = json_decode($this->client->get('/test')->getBody()->getContents(), true);
        self::assertEquals($expectedBody, $result);
    }

    public function testItVerifiesOtherRequest(): void
    {
        $expectedBody = ['someKey' => 'otherValue'];

        $this->mockTestRequest((string) json_encode($expectedBody));

        $result = json_decode($this->client->get('/test')->getBody()->getContents(), true);
        self::assertEquals($expectedBody, $result);
    }

    public function testItFailsAsExpected(): void
    {
        $this->mockTestPostRequest(
            (string) json_encode(['someKey' => 'someValue']),
            (string) json_encode(['data' => 'whatever'])
        );

        $this->client->post('/test');

        self::assertTrue(true);
    }
}
