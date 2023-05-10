<?php

declare(strict_types=1);

namespace WireMock\Phpunit\Exception;

final class StartException extends \RuntimeException
{
    private function __construct(string $message = "")
    {
        parent::__construct($message);
    }

    public static function missingParameters(): self
    {
        return new self("Missing extension parameters, `host` and `port` must be set");
    }

    public static function timeout(int $time): self
    {
        return new self("Failed to connect to wiremock server after $time seconds");
    }
}
