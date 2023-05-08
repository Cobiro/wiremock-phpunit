<?php

declare(strict_types=1);

namespace WireMock\Phpunit\Extension;

use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use WireMock\Phpunit\Extension\Subscriber\Reset;
use WireMock\Phpunit\Extension\Subscriber\StartWireMock;
use WireMock\Phpunit\Extension\Subscriber\Verify;

final class WireMockExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $timeout = (int) ($parameters->has('timeout') ? $parameters->get('timeout') :3);

        $facade->registerSubscribers(
            new StartWireMock(
                $parameters->get('host'),
                $parameters->get('port'),
                $timeout
            ),
            new Verify(),
            new Reset()
        );
    }
}
