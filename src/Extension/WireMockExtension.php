<?php

declare(strict_types=1);

namespace WireMock\Phpunit\Extension;

use PHPUnit\Runner\BeforeFirstTestHook;
use PHPUnit\Runner\BeforeTestHook;
use WireMock\Phpunit\WireMockProxy;

final class WireMockExtension implements BeforeFirstTestHook, BeforeTestHook
{
    public function executeBeforeFirstTest(): void
    {
        WireMockProxy::startWireMock();
    }

    public function executeBeforeTest(string $test): void
    {
        WireMockProxy::reset();
    }
}
