<?php

declare(strict_types=1);


namespace DTL\Depgraph\Tests\Unit;

use DTL\Depgraph\DepgraphFactory;
use DTL\Depgraph\Tests\Support\TestLogger;

final class DepgraphFactoryTest extends TestCase
{
    public function testDisablesPlugins(): void
    {
        $composer = DepgraphFactory::default()->createComposer(
            __DIR__ . '/Composer/example/simple/composer.json'
        );
        static::assertTrue($composer->getPluginManager()->arePluginsDisabled('local'));
        static::assertTrue($composer->getPluginManager()->arePluginsDisabled('global'));
    }

    public function testDisablesScripts(): void
    {
        $logger = new TestLogger();
        $composer = DepgraphFactory::default(logger: $logger)->createComposer(
            __DIR__ . '/Composer/example/simple/composer.json',
        );

        $dispatcher = $composer->getEventDispatcher();

        // there's no great way to determine if run-scripts is enabled or not,
        // so we pick the value with reflection
        $reflection = new \ReflectionClass($dispatcher);
        $runScripts = $reflection->getProperty('runScripts');

        static::assertFalse($runScripts->getValue($dispatcher));
    }
}
