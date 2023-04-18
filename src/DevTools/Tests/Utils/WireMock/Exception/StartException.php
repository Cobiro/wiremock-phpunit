<?php

declare(strict_types=1);

namespace Cobiro\DevTools\Tests\Utils\WireMock\Exception;

final class StartException extends \RuntimeException
{
    private function __construct(string $message = "")
    {
        parent::__construct($message);
    }

    public static function missingEnv(): self
    {
        return new self("Missing env variables, WIREMOCK_HOST and WIREMOCK_PORT must be set");
    }

    public static function timeout(int $time): self
    {
        return new self("Failed to connect to wiremock server after $time seconds");
    }
}
