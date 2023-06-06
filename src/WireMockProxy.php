<?php

declare(strict_types=1);

namespace WireMock\Phpunit;

use WireMock\Phpunit\Exception\RequestVerificationException;
use WireMock\Phpunit\Exception\StartException;
use WireMock\Phpunit\Exception\VerifyException;
use WireMock\Client\WireMock;

final class WireMockProxy
{
    /** @var array<callable> */
    public static array $verifyCallbacks = [];
    public static ?WireMock $wireMock = null;

    public static function startWireMock(
        string $host,
        string $port,
        int $timeout
    ): void {
        if (self::$wireMock !== null) {
            return;
        }

        if ($host === '' || $port === '') {
            throw StartException::missingParameters();
        }

        self::$wireMock = WireMock::create($host, $port);
        $serverStarted = self::$wireMock->isAlive($timeout);

        if (!$serverStarted) {
            throw StartException::timeout($timeout);
        }
    }

    public static function reset(): void
    {
        if (self::$wireMock === null) {
            return;
        }

        if (WireMockProxy::$verifyCallbacks !== []) {
            return;
        }

        self::$wireMock->reset();
        WireMockProxy::$verifyCallbacks = [];
    }

    public static function verify(string $test): void
    {
        $thrownExceptions = [];

        try {
            foreach (WireMockProxy::$verifyCallbacks as $verifyCallback) {
                $verifyCallback();
            }
        } catch (RequestVerificationException $exception) {
            $thrownExceptions[] = $exception;
        }

        if (count($thrownExceptions) > 0) {
            WireMockProxy::$verifyCallbacks = [];

            throw new VerifyException($test, ...$thrownExceptions);
        }
    }

    public static function instance(): WireMock
    {
        if (self::$wireMock === null) {
            throw new \RuntimeException('Missing wiremock instance');
        }

        return self::$wireMock;
    }
}
