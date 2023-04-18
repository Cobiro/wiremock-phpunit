<?php

declare(strict_types=1);

namespace Cobiro\DevTools\Tests\Utils\WireMock;

use Cobiro\DevTools\Tests\Utils\WireMock\Exception\VerifyException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;
use PHPUnit\Runner\BeforeTestHook;

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
