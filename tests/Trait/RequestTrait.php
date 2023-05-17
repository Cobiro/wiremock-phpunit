<?php

declare(strict_types=1);

namespace Tests\Trait;

use WireMock\Phpunit\WireMockRequestBodyType;
use WireMock\Phpunit\WireMockTrait;

trait RequestTrait
{
    use WireMockTrait;

    public function mockTestRequest(string $expectedBody): void
    {
        $this->wireMock(
            'GET',
            '/test',
            [],
            null,
            [],
            $expectedBody
        );
    }

    public function mockTestPostRequest(string $expectedBody, string $requestBody): void
    {
        $this->wireMock(
            'POST',
            '/test',
            [],
            $requestBody,
            [],
            $expectedBody
        );
    }

    public function mockTestPostRequestWithXML(string $expectedBody, string $requestBody): void
    {
        $this->wireMock(
            'POST',
            '/test',
            [],
            $requestBody,
            [],
            $expectedBody,
            200,
            WireMockRequestBodyType::XML
        );
    }
}
