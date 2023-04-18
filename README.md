## PHPUnit extension for WireMock

This library provides easy way to integrate [WireMock](https://wiremock.org) with PHPUnit. It will verify mocked interactions after each test and fail them if they are not met.

### Requirements

- Running wiremock service(in tests we use `wiremock/wiremock:latest`)
- Set up `WIREMOCK_HOST` and `WIREMOCK_PORT` in env variables of `phpunit.xml`
- PHP 8
- PHPUnit 9.3 or higher
- wiremock-php 2.0 or higher

### Usage

There are few ways to use this extension, each varies on level on how deeply you are open to modify your tests. First you need to add extension to your `phpunit.xml` configuration:

```xml
<extensions>
    <extension class="CLASS_NAME"/>
</extensions>
```

We suggest using this extension for most convenient usage:

#### Cobiro\DevTools\Tests\Utils\WireMock\WireMockExtension

This extension doesn't provide verification after each test. To verify interactions your test case need to extend `Cobiro\DevTools\Tests\Utils\WireMock\WireMockTestCase` or you need to use `Cobiro\DevTools\Tests\Utils\WireMock\WireMockVerificationTrait`. Both solutions are triggering verification on teardown. Using this extension gives you nice response of failed tests. Also it doesn't stop on first failed verification.

#### Cobiro\DevTools\Tests\Utils\WireMock\WireMockVerificationExtension

This extension triggers verification automatically after each test, but it comes with caveat that it fails after first failed interaction, so we are not recommending this way. But with this you don't need to extend our test case or add a new trait to existing test cases.

#### Mocking requests

To mock requests you need to use `Cobiro\DevTools\Tests\Utils\WireMock\WireMockTrait::wireMock()`. We recommend creating your own traits with your own methods. This is one of examples:

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

By default extension waits for 3 seconds for wiremock server. If you need more time you can change it by setting environment variable `WIREMOCK_TIMEOUT`
