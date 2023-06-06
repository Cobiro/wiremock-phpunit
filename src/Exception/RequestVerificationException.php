<?php

declare(strict_types=1);

namespace WireMock\Phpunit\Exception;

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
        \Throwable $wireMockException
    ): self {
        return new self(
            sprintf(
                "Failed to verify interactions for path $url and method $method due to: %s. For more check wiremock logs.",
                $wireMockException->getMessage() . PHP_EOL
            ),
            $wireMockException
        );
    }

    public static function clientException(
        string $url,
        string $method,
        \Throwable $exception
    ): self {
        return new self(
            "Request to path $url and method $method failed due to: {$exception->getMessage()}e",
            $exception
        );
    }
}
