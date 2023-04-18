<?php

declare(strict_types=1);

namespace Cobiro\DevTools\Tests\Utils\WireMock;

use PHPUnit\Framework\TestCase;

abstract class WireMockTestCase extends TestCase
{
    use WireMockVerificationTrait;
}
