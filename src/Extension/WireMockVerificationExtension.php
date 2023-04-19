<?php

declare(strict_types=1);

namespace WireMock\Phpunit\Extension;

use WireMock\Phpunit\Exception\VerifyException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;
use PHPUnit\Runner\BeforeTestHook;
use WireMock\Phpunit\WireMockProxy;

final class WireMockVerificationExtension implements BeforeFirstTestHook, AfterTestHook, BeforeTestHook
{
    public function executeBeforeFirstTest(): void
    {
        WireMockProxy::startWireMock();
    }

    public function executeBeforeTest(string $test): void
    {
        WireMockProxy::reset();
    }

    public function executeAfterTest(string $test, float $time): void
    {
        try {
            WireMockProxy::verify($test);
        } catch (VerifyException $exception) {
            TestCase::fail($exception->getMessage());
        }
    }
}
