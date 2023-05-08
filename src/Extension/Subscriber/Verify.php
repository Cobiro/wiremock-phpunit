<?php

declare(strict_types=1);

namespace WireMock\Phpunit\Extension\Subscriber;

use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;
use WireMock\Phpunit\Exception\VerifyException;
use WireMock\Phpunit\WireMockProxy;

final class Verify implements FinishedSubscriber
{
    public function notify(Finished $event): void
    {
        try {
            WireMockProxy::verify($event->test()->name());
        } catch (VerifyException $exception) {
            Facade::emitter()->testFailed(
                $event->test(),
                ThrowableBuilder::from($exception),
                null
            );
        }
    }
}
