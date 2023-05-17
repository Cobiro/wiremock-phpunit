<?php

declare(strict_types=1);

namespace WireMock\Phpunit;

use WireMock\Phpunit\Exception\RequestVerificationException;
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
        ?string $requestContentType = null
    ): void {
        $response = $this->wireResponse($responseStatusCode, $responseBody, $responseHeaders);

        WireMockProxy::instance()->stubFor($this->wireRequest($method, $path)->willReturn($response));

        // wire request
        WireMockProxy::$verifyCallbacks[] = function () use ($method, $path, $requestBody, $requestHeaders, $requestContentType) {
            $requestPatternBuilder = $this->wireMethodRequestedFor($method, $path);

            $requestBodyPatternBuilder = $requestBody;

            if (is_array($requestBodyPatternBuilder)) {
                $requestBodyPatternBuilder = json_encode($requestBodyPatternBuilder, JSON_THROW_ON_ERROR);

                if ($requestContentType === null) {
                    $requestContentType = WireMockRequestBodyType::JSON;
                }
            }

            if ($requestBodyPatternBuilder !== null ) {
                $equalTo = match ($requestContentType) {
                    WireMockRequestBodyType::JSON => WireMock::equalToJson($requestBodyPatternBuilder),
                    WireMockRequestBodyType::XML => WireMock::equalToXml($requestBodyPatternBuilder),
                    default => WireMock::equalTo($requestBodyPatternBuilder),
                };

                $requestPatternBuilder->withRequestBody($equalTo);
            }

            foreach ($requestHeaders as $name => $value) {
                $requestPatternBuilder->withHeader($name, WireMock::equalTo($value));
            }

            try {
                WireMockProxy::instance()->verify($requestPatternBuilder);
            } catch (VerificationException $verificationException) { // @phpstan-ignore-line
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
