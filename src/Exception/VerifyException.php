<?php

declare(strict_types=1);

namespace WireMock\Phpunit\Exception;

final class VerifyException extends \RuntimeException
{
    public function __construct(string $testName, RequestVerificationException ...$exceptions)
    {
        $message = $this->renderMessage($testName, ...$exceptions);
        parent::__construct($message);
    }

    private function renderMessage(string $testName, RequestVerificationException ...$exceptions): string
    {
        $exceptionMessages = [];

        foreach ($exceptions as $exception) {
            $exceptionMessages[] = $exception->getMessage();
        }

        return sprintf(
            'WireMock verification failed for test %s, there were %s exceptions thrown: %s',
            $testName,
            count($exceptionMessages),
            PHP_EOL . PHP_EOL . implode(PHP_EOL, $exceptionMessages)
        );
    }
}
