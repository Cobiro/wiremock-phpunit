<?php

declare(strict_types=1);

namespace Cobiro\DevTools\Tests\Utils\WireMock;

use PHPUnit\Runner\BeforeFirstTestHook;
use PHPUnit\Runner\BeforeTestHook;

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
