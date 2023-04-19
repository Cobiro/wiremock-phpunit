<?php

declare(strict_types=1);

namespace WireMock\Phpunit;

use WireMock\Phpunit\Exception\VerifyException;
use PHPUnit\Framework\TestCase;

trait WireMockVerificationTrait
{
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->verifyInteractions();
    }

    protected function verifyInteractions(): void
    {
        try {
            WireMockProxy::verify(static::class . ':' . $this->getName());
        } catch (VerifyException $exception) {
            TestCase::fail($exception->getMessage());
        }
    }
}
