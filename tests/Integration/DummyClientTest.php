<?php

declare(strict_types=1);

namespace Tests\Integration;

use GuzzleHttp\Client;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Tests\Trait\RequestTrait;
use WireMock\Phpunit\Exception\VerifyException;

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

    public function testItFailsAsExpectedSecondTime(): void
    {
        $this->mockTestPostRequest(
            (string) json_encode(['someKey' => 'someValue']),
            (string) json_encode(['data' => 'whatever'])
        );

        $this->client->post('/test');

        self::assertTrue(true);
    }
}
