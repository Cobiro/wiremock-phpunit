## PHPUnit extension for WireMock

This library provides easy way to integrate [WireMock](https://wiremock.org) with PHPUnit. It will verify mocked interactions after each test and fail them if they are not met.

### Requirements

- Running wiremock service(in tests we use `wiremock/wiremock:latest`)
- Set up `host` and `port` as parameters of extension(see below)
- PHP 8.1
- PHPUnit 10 or higher
- wiremock-php 2.0 or higher

### Usage

To use extension add to your phpunit.xml configuration:

```xml
<extensions>
    <bootstrap class="WireMock\Phpunit\Extension\WireMockExtension">
        <parameter name="host" value="wiremock"/>
        <parameter name="port" value="8080"/>
    </bootstrap>
</extensions>
```

It listens for those events and triggers specific actions:
- `PHPUnit\Event\Test\PreparationStarted` - Resets wiremock interactions
- `PHPUnit\Event\TestRunner\BootstrapFinished` - Starts wiremock instance
- `PHPUnit\Event\Test\Finished` - Verifies interactions

#### Mocking requests

To mock requests you need to use `WireMock\Phpunit\WireMockTrait::wireMock()`. We recommend creating your own traits with your own methods. This is one of examples:

```php
trait RequestTrait
{
    use WireMockTrait;

    public function mockTestRequest(string $expectedBody): void
    {
        $this->wireMock(
            'GET',
            '/test',
            [],
            null,
            [],
            $expectedBody
        );
    }

    public function mockTestPostRequest(string $expectedBody, string $requestBody): void
    {
        $this->wireMock(
            'POST',
            '/test',
            [],
            $requestBody,
            [],
            $expectedBody
        );
    }
}
```

### Configuration

By default, extension waits for 3 seconds for wiremock server. If you need more time you can change it by setting parameter `timeout` in phpunit configuration
