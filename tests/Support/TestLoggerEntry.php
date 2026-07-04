<?php

declare(strict_types=1);


namespace DTL\Depgraph\Tests\Support;

final readonly class TestLoggerEntry
{
    public function __construct(public string $level, public string $message, public array $context = [])
    {
    }
}
