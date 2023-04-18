<?php

declare(strict_types=1);

namespace Cobiro\DevTools\Tests\Utils\WireMock\Exception;

use GuzzleHttp\Exception\ClientException;
use WireMock\Client\VerificationException;

final class RequestVerificationException extends \Exception
{
    private function __construct(
        string $message,
        \Throwable $previous
    ) {
        parent::__construct($message, previous: $previous);
    }

    public static function verificationFailed(
        string $url,
        string $method,
        VerificationException $wireMockException
    ): self {
        return new self(
            "Failed to verify interactions for path $url and method $method, for more check wiremock logs",
            $wireMockException
        );
    }

    public static function clientException(
        string $url,
        string $method,
        ClientException $exception
    ): self {
        return new self(
            "Request to path $url and method $method failed due to: {$exception->getMessage()}e",
            $exception
        );
    }
}
