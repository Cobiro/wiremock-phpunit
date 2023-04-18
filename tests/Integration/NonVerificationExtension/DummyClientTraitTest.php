<?php

declare(strict_types=1);

namespace Tests\Integration;

use Cobiro\DevTools\Tests\Utils\WireMock\WireMockVerificationTrait;
use GuzzleHttp\Client;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Tests\Trait\RequestTrait;

final class DummyClientTraitTest extends TestCase
{
    use RequestTrait, WireMockVerificationTrait;

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

        $this->mockTestRequest(json_encode($expectedBody));

        $result = json_decode($this->client->get('/test')->getBody()->getContents(), true);
        self::assertEquals($expectedBody, $result);
    }

    public function testItVerifiesOtherRequest(): void
    {
        $expectedBody = ['someKey' => 'otherValue'];

        $this->mockTestRequest(json_encode($expectedBody));

        $result = json_decode($this->client->get('/test')->getBody()->getContents(), true);
        self::assertEquals($expectedBody, $result);
    }

    public function testItFailsAsExpected(): void
    {
        $this->mockTestPostRequest(
            json_encode(['someKey' => 'someValue']),
            json_encode(['data' => 'whatever'])
        );

        $this->client->post('/test');

        self::assertTrue(true);
    }

    public function testItFailsAsExpectedSecondTime(): void
    {
        $this->mockTestPostRequest(
            json_encode(['someKey' => 'someValue']),
            json_encode(['data' => 'whatever'])
        );

        $this->client->post('/test');

        self::assertTrue(true);
    }
}
