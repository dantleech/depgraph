<?php

namespace DTL\Depgraph;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use DTL\Depgraph\Command\DepgraphCommand;
use Override;

class DepgraphPlugin implements PluginInterface, EventSubscriberInterface, Capable, CommandProvider
{
    /**
     * @var Composer
     */
    private $composer;

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
        ];
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getCapabilities(): array
    {
        return [
            CommandProvider::class => self::class
        ];
    }

    #[Override]
    public function getCommands(): array
    {
        return [
            new DepgraphCommand(
                new 
            ),
        ];
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function uninstall(Composer$composer, IOInterface $io): void
    {
    }
}
