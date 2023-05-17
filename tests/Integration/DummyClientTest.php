<?php

declare(strict_types=1);

namespace Tests\Integration;

use GuzzleHttp\Client;
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

    public function testItVerifiesRequest(): void
    {
        $expectedBody = ['someKey' => 'someValue'];

        $this->mockTestRequest((string) json_encode($expectedBody));

        $result = json_decode($this->client->get('/test')->getBody()->getContents(), true);
        self::assertEquals($expectedBody, $result);
    }

    public function testItVerifiesXmlRequest(): void
    {
        $expectedBody = 'OK';

        $this->mockTestPostRequestWithXML($expectedBody, '<?xml version="1.0"?>
<result><email>arjun76@mertz.info</email><name>Julien Runte</name><phone>1-985-265-5979</phone><description>Ipsum nulla sed autem enim id quaerat. Explicabo alias inventore qui autem tempora esse. Assumenda quam magni dolorem. Culpa labore in ratione modi aliquam velit asperiores.</description></result>
');

        $result = $this->client->post('/test', ['body' => '<?xml version="1.0"?>
<result><phone>1-985-265-5979</phone><email>arjun76@mertz.info</email><name>Julien Runte</name><description>Ipsum nulla sed autem enim id quaerat. Explicabo alias inventore qui autem tempora esse. Assumenda quam magni dolorem. Culpa labore in ratione modi aliquam velit asperiores.</description></result>
'])->getBody()->getContents();
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
