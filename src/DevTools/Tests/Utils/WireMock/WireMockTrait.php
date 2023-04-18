<?php

declare(strict_types=1);

namespace Cobiro\DevTools\Tests\Utils\WireMock;

use Cobiro\DevTools\Tests\Utils\WireMock\Exception\RequestVerificationException;
use GuzzleHttp\Exception\ClientException;
use WireMock\Client\MappingBuilder;
use WireMock\Client\RequestPatternBuilder;
use WireMock\Client\ResponseDefinitionBuilder;
use WireMock\Client\VerificationException;
use WireMock\Client\WireMock;

trait WireMockTrait
{
    protected function wireMock(
        string            $method,
        string            $path,
        array            $requestHeaders = [],
        array|string|null $requestBody = null,
        array            $responseHeaders = [],
        array|string|null $responseBody = null,
        int               $responseStatusCode = 200,
    ) {
        $response = $this->wireResponse($responseStatusCode, $responseBody, $responseHeaders);

        WireMockProxy::$wireMock->stubFor($this->wireRequest($method, $path)->willReturn($response));

        // wire request
        WireMockProxy::$verifyCallbacks[] = function () use ($method, $path, $requestBody, $requestHeaders) {
            $requestPatternBuilder = $this->wireMethodRequestedFor($method, $path);

            if (is_array($requestBody)) {
                $requestPatternBuilder->withRequestBody(WireMock::equalTo(json_encode($requestBody, JSON_THROW_ON_ERROR)));
            }

            if (is_string($requestBody)) {
                $requestPatternBuilder->withRequestBody(WireMock::equalTo($requestBody));
            }

            foreach ($requestHeaders as $name => $value) {
                $requestPatternBuilder->withHeader($name, WireMock::equalTo($value));
            }

            try {
                WireMockProxy::$wireMock->verify($requestPatternBuilder);
            } catch (VerificationException $verificationException) {
                throw RequestVerificationException::verificationFailed(
                    $path,
                    $method,
                    $verificationException
                );
            } catch (ClientException $clientException) {
                throw RequestVerificationException::clientException(
                    $path,
                    $method,
                    $clientException
                );
            }
        };
    }

    private function wireRequest(string $method, string $path): MappingBuilder
    {
        return new MappingBuilder(new RequestPatternBuilder(strtoupper($method), WireMock::urlEqualTo($path)));
    }

    private function wireMethodRequestedFor(string $method, string $path): RequestPatternBuilder
    {
        return new RequestPatternBuilder(strtoupper($method), WireMock::urlEqualTo($path));
    }

    private function wireResponse(
        int $responseStatusCode,
        array|string|null $responseBody,
        array $responseHeaders
    ): ResponseDefinitionBuilder {
        $response = WireMock::aResponse()->withStatus($responseStatusCode);

        if (is_array($responseBody)) {
            $response->withBody(json_encode($responseBody, JSON_THROW_ON_ERROR));
        }

        if (is_string($responseBody)) {
            $response->withBody($responseBody);
        }

        foreach ($responseHeaders as $name => $value) {
            $response->withHeader($name, $value);
        }
        
        return $response;
    }
}
