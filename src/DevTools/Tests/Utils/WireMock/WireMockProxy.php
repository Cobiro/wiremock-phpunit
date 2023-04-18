<?php

declare(strict_types=1);

namespace Cobiro\DevTools\Tests\Utils\WireMock;

use Cobiro\DevTools\Tests\Utils\WireMock\Exception\RequestVerificationException;
use Cobiro\DevTools\Tests\Utils\WireMock\Exception\StartException;
use Cobiro\DevTools\Tests\Utils\WireMock\Exception\VerifyException;
use WireMock\Client\WireMock;

final class WireMockProxy
{
    private const ENV_HOST = 'WIREMOCK_HOST';
    private const ENV_PORT = 'WIREMOCK_PORT';
    private const ENV_TIMEOUT = 'WIREMOCK_TIMEOUT';

    /** @var array<callable> */
    public static array $verifyCallbacks = [];
    public static ?WireMock $wireMock = null;

    public static function startWireMock(): void
    {
        if (self::$wireMock !== null) {
            return;
        }

        $wiremockHost = getenv(self::ENV_HOST);
        $wiremockPort = getenv(self::ENV_PORT);
        $wiremockTimeout = (int) (getenv(self::ENV_TIMEOUT) ? getenv(self::ENV_TIMEOUT) : 3);

        if (!$wiremockHost || !$wiremockPort) {
            throw StartException::missingEnv();
        }

        self::$wireMock = WireMock::create($wiremockHost, $wiremockPort);
        $serverStarted = self::$wireMock->isAlive($wiremockTimeout);

        if (!$serverStarted) {
            throw StartException::timeout($wiremockTimeout);
        }
    }

    public static function reset(): void
    {
        if (self::$wireMock === null) {
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

        if (WireMockProxy::$verifyCallbacks !== []) {
            WireMockProxy::$verifyCallbacks = [];
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
