<?php

declare(strict_types=1);

namespace WireMock\Phpunit\Extension\Subscriber;

use PHPUnit\Event\TestRunner\BootstrapFinished;
use PHPUnit\Event\TestRunner\BootstrapFinishedSubscriber;
use WireMock\Phpunit\WireMockProxy;

final class StartWireMock implements BootstrapFinishedSubscriber
{
    public function __construct(
        private readonly string $host,
        private readonly string $port,
        private readonly int $timeout
    ) {
    }

    public function notify(BootstrapFinished $event): void
    {
        WireMockProxy::startWireMock(
            $this->host,
            $this->port,
            $this->timeout
        );
    }
}
