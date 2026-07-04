<?php

declare(strict_types=1);


namespace DTL\Depgraph\Tests\Support;

use Override;
use Psr\Log\AbstractLogger;
use Stringable;

final class TestLogger extends AbstractLogger
{
    /**
     * @param list<TestLoggerEntry> $logs
     */
    public function __construct(private array $logs = [])
    {
    }

    #[Override]
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        if (!is_string($level)) {
            throw new \RuntimeException('not a string');
        }
        $this->logs[] = new TestLoggerEntry($level, (string)$message, $context);
    }

    public function count(): int
    {
        return count($this->logs);
    }

    public function at(int $index): TestLoggerEntry
    {
        if (!array_key_exists($index, $this->logs)) {
            throw new \RuntimeException(sprintf(
                'No log entry at index %d, we have %d entries',
                $index, $this->count()
            ));
        }

        return $this->logs[$index];
    }

    public function containingMessage(string $message): self
    {
        return new self(array_values(array_filter($this->logs, static fn (TestLoggerEntry $entry) => str_contains($entry->message, $message))));
    }

}
