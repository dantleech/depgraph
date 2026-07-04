<?php

declare(strict_types=1);


namespace DTL\Depgraph\Console;

use Override;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Stringable;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleLogger extends AbstractLogger
{
    public function __construct(private OutputInterface $output)
    {
    }

    #[Override]
    public function log(
        $level,
        string|\Stringable $message,
        array $context = []
    ): void
    {
        if (
            OutputInterface::VERBOSITY_VERBOSE >= $this->output->getVerbosity() && 
            $level === LogLevel::DEBUG
        ) {
            return;
        }

        // why is this mixed!?
        if (!is_string($level)) {
            return;
        }

        $style = match ($level) {
            LogLevel::DEBUG => 'fg=#aaa',
            LogLevel::INFO => 'fg=green',
            default => 'fg=cyan',
        };

        $this->output->writeln(sprintf(
            '[<%s>%s</>] <fg=#aaa>%s</>', $style, $level, $message
        ));
    }
}
