<?php

declare(strict_types=1);

namespace WireMock\Phpunit;

use WireMock\Client\ValueMatchingStrategy;
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
        ?string $requestContentType = null,
        bool $stubRequestBody = false
    ): void {
        $response = $this->wireResponse($responseStatusCode, $responseBody, $responseHeaders);

        $request = $this->wireRequest($method, $path);

        $requestBodyMatchingStrategy = $this->requestBodyMatchingStrategy($requestBody, $requestContentType);

        if ($requestBodyMatchingStrategy !== null && $stubRequestBody) {
            $request->withRequestBody($requestBodyMatchingStrategy);
        }

        WireMockProxy::instance()->stubFor($request->willReturn($response));

        // wire request
        WireMockProxy::$verifyCallbacks[] = function () use ($method, $path, $requestBody, $requestHeaders, $requestContentType, $requestBodyMatchingStrategy) {
            $requestPatternBuilder = $this->wireMethodRequestedFor($method, $path);

            if ($requestBodyMatchingStrategy !== null) {
                $requestPatternBuilder->withRequestBody($requestBodyMatchingStrategy);
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

    private function requestBodyMatchingStrategy(
        array|string|null $requestBody,
        ?string $requestContentType
    ): ?ValueMatchingStrategy {
        if (is_array($requestBody)) {
            $requestBody = json_encode($requestBody, JSON_THROW_ON_ERROR);

            if ($requestContentType === null) {
                $requestContentType = WireMockRequestBodyType::JSON;
            }
        }

        if ($requestBody !== null ) {
            $equalTo = match ($requestContentType) {
                WireMockRequestBodyType::JSON => WireMock::equalToJson($requestBody),
                WireMockRequestBodyType::XML => WireMock::equalToXml($requestBody),
                default => WireMock::equalTo($requestBody),
            };

            return $equalTo;
        }

        return null;
    }
}
