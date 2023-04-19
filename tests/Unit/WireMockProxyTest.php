<?php

declare(strict_types=1);

namespace Tests\Unit;

use WireMock\Phpunit\Exception\StartException;
use WireMock\Phpunit\Exception\VerifyException;
use WireMock\Phpunit\WireMockProxy;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Tests\Trait\RequestTrait;

final class WireMockProxyTest extends TestCase
{
    use RequestTrait;

    protected function tearDown(): void
    {
        parent::tearDown();
        WireMockProxy::$wireMock = null;
    }

    public function testItStartsWireMock(): void
    {
        putenv('WIREMOCK_HOST=wiremock');
        putenv('WIREMOCK_PORT=8080');

        WireMockProxy::startWireMock();

        $content = file_get_contents('http://wiremock:8080/__admin/');

        self::assertNotEmpty($content);
    }

    public function testItFailsToStartWhenWrongHost(): void
    {
        putenv('WIREMOCK_HOST=whatever');
        putenv('WIREMOCK_PORT=8080');

        $this->expectException(StartException::class);

        WireMockProxy::startWireMock();
    }

    public function testItFailsOnMissingEnvHost(): void
    {
        putenv('WIREMOCK_HOST');
        putenv('WIREMOCK_PORT=8080');

        $this->expectException(StartException::class);

        WireMockProxy::startWireMock();
    }

    public function testItVerifiesInteraction(): void
    {
        putenv('WIREMOCK_HOST=wiremock');
        putenv('WIREMOCK_PORT=8080');

        WireMockProxy::startWireMock();

        $expectedBody = json_encode(['someKey' => 'someValue']);

        $this->mockTestRequest((string) $expectedBody);

        $client = new Client([
            'base_uri' => 'http://wiremock:8080',
        ]);

        $response = $client->get('/test')->getBody()->getContents();
        self::assertEquals($expectedBody, $response);

        try {
            WireMockProxy::verify('random-test');
        } catch (\Throwable $exception) {
            $this->fail('should not catch exception');
        }
    }

    public function testItThrowsExceptionOnFailedVerification(): void
    {
        putenv('WIREMOCK_HOST=wiremock');
        putenv('WIREMOCK_PORT=8080');

        WireMockProxy::startWireMock();

        $expectedBody = json_encode(['someKey' => 'someValue']);

        $this->mockTestPostRequest((string) $expectedBody, (string) json_encode(['some-body' => 'whatever']));

        $client = new Client([
            'base_uri' => 'http://wiremock:8080',
        ]);

        $client->post('/test')->getBody()->getContents();

        $this->expectException(VerifyException::class);
        WireMockProxy::verify('random-test');
    }
}
