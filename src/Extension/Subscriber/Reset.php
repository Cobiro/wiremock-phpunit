<?php

declare(strict_types=1);

namespace WireMock\Phpunit\Extension\Subscriber;

use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;
use WireMock\Phpunit\WireMockProxy;

final class Reset implements PreparationStartedSubscriber
{
    public function notify(PreparationStarted $event): void
    {
        WireMockProxy::reset();
    }
}
