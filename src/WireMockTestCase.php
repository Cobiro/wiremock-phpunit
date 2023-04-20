<?php

declare(strict_types=1);

namespace WireMock\Phpunit;

use PHPUnit\Framework\TestCase;

abstract class WireMockTestCase extends TestCase
{
    use WireMockVerificationTrait;
}
