<?php

declare(strict_types=1);

namespace DTL\Depgraph\Tests\Unit\Composer;

use DTL\Depgraph\DepgraphFactory;

use DTL\Depgraph\Graph\Graph;
use DTL\Depgraph\Tests\Unit\TestCase;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;

final class ComposerGraphFactoryTest extends TestCase
{
    #[DataProvider('provideFactory')]
    public function testFactory(string $example, Graph $expected): void
    {
        $composer = DepgraphFactory::default()->createComposer(
            sprintf('%s/example/%s/composer.json', __DIR__, $example)
        );

        $graph = DepgraphFactory::default()->createGraphFactory($composer)->getGraph();

        static::assertEquals($expected, $graph);
    }

    /**
     * @return Generator<array{string,Graph}>
     */
    public static function provideFactory(): Generator
    {
        yield [
            'simple',
            Graph::new([
                self::createPackage('daniel/simple', version: 'dev-main', licence: []),
                self::createPackage('phpactor/container', version: '3.0.1', licence: ['MIT']),
                self::createPackage('phpactor/map-resolver', version: '1.7.0', licence: ['MIT']),
                self::createPackage('psr/container', version: '2.0.2', licence: ['MIT']),
                self::createPackage('psr/log', version: '3.0.2', licence: ['MIT']),
            ], [
                'daniel/simple' => [
                    self::createConstraint('phpactor/container', '^3.0', direct: true),
                    self::createConstraint('psr/log', '^3.0', dev: true, direct: true),
                ],
                'phpactor/container' => [
                    self::createConstraint('php', '^8.1'),
                    self::createConstraint('phpactor/map-resolver', '^1.4'),
                    self::createConstraint('psr/container', '^1.0||^2.0'),
                ],
                'phpactor/map-resolver' => [
                    self::createConstraint('php', '^8.1'),
                ],
                'psr/container' => [
                    self::createConstraint('php', '>=7.4.0'),
                ],
                'psr/log' => [
                    self::createConstraint('php', '>=8.0.0', true),
                ],
            ])
        ];
        yield 'dev flag depends on being reachable by prod/requires dependencies' => [
            'prod-precedence',
            Graph::new([
                self::createPackage('__root__', version: 'dev-main'),
                self::createPackage('psr/container', version: '2.0.2', licence: ['MIT']),
                self::createPackage('phpactor/container', version: '3.0.1', licence: ['MIT']),
                self::createPackage('phpactor/map-resolver', version: '1.7.0', licence: ['MIT']),
            ], [
                'phpactor/container' => [
                    self::createConstraint('php', '^8.1', dev: true),
                    self::createConstraint('phpactor/map-resolver', '^1.4', dev: true),
                    self::createConstraint('psr/container', '^1.0||^2.0', dev: true),
                ],
                'phpactor/map-resolver' => [
                    self::createConstraint('php', '^8.1', true),
                ],
                'psr/container' => [
                    self::createConstraint('php', '>=7.4.0'),
                ],
                '__root__' => [
                    self::createConstraint('psr/container', '^2.0', dev: false, direct: true),
                    self::createConstraint('phpactor/container', '^3.0', dev: true, direct: true),
                ],
            ])
        ];
        yield [
            'missing-lock',
            Graph::new([
                self::createPackage('daniel/simple', version: 'dev-main', licence: []),
                self::createPackage('phpactor/container', version: '?', licence: []),
                self::createPackage('psr/log', version: '?', licence: []),
            ], [
                'daniel/simple' => [
                    self::createConstraint('phpactor/container', '^3.0', direct: true),
                    self::createConstraint('psr/log', '^3.0', direct: true, dev: true),
                ],
            ])
        ];
    }

    #[DataProvider('provideFactoryFailure')]
    public function testFactoryFailure(string $example, string $expectedExceptionMessage): void
    {
        $this->expectExceptionMessageIs($expectedExceptionMessage);

        $composer = DepgraphFactory::default()->createComposer(
            sprintf('%s/example/%s/composer.json', __DIR__, $example)
        );

        DepgraphFactory::default()->createGraphFactory($composer)->getGraph();
    }



    /**
     * @return Generator<array{string,string}>
     */
    public static function provideFactoryFailure(): Generator
    {
        yield 'dangling' => [
            'dangling',
            implode('', [
                'More than one root node in dependency tree: "phpactor/container", ', 
                '"psr/log", "daniel/dangling" - this can mean that the composer.json', 
                ' file is out of sync with the lock file',
            ]),
        ];
    }

    public function testDistanceAssignment(): void
    {
        $graph = Graph::new([
            self::createPackage('daniel/simple', version: 'dev-main', licence: []),
        ], [
            'daniel/simple' => [
                self::createConstraint('phpactor/container', '^3.0', direct: true),
                self::createConstraint('psr/log', '^3.0', direct: true, dev: true),
                self::createConstraint('car/dar', '^3.0', direct: true, dev: true),
            ],
            'psr/log' => [
                self::createConstraint('foo/bar', '^3.0', direct: true, dev: true),
                self::createConstraint('bar/foo', '^3.0', direct: true, dev: true),
                self::createConstraint('car/dar', '^3.0', direct: true, dev: true),
            ],
            'foo/bar' => [
                self::createConstraint('dee/bee', '^3.0', direct: true, dev: true),
            ],
            'dee/bee' => [
                self::createConstraint('eee/ddd', '^3.0', direct: true, dev: true),
            ],
        ]);

        static::assertSame(0, $graph->package('daniel/simple')->distance);
        static::assertSame(1, $graph->package('phpactor/container')->distance);
        static::assertSame(2, $graph->package('bar/foo')->distance);
        static::assertSame(2, $graph->package('foo/bar')->distance);
        static::assertSame(1, $graph->package('car/dar')->distance);
        static::assertSame(3, $graph->package('dee/bee')->distance);
        static::assertSame(4, $graph->package('eee/ddd')->distance);

        $graph = Graph::new([
            self::createPackage('daniel/simple', version: 'dev-main', licence: []),
        ], [
            'daniel/simple' => [
                self::createConstraint('psr/log', '^3.0', direct: true, dev: true),
            ],
            'psr/log' => [
                self::createConstraint('foo/bar', '^3.0', direct: true, dev: true),
                self::createConstraint('bar/foo', '^3.0', direct: true, dev: true),
                self::createConstraint('dee/bee', '^3.0', direct: true, dev: true),
                self::createConstraint('car/dar', '^3.0', direct: true, dev: true),
            ],
            'foo/bar' => [
                self::createConstraint('dee/bee', '^3.0', direct: true, dev: true),
            ],
            'dee/bee' => [
                self::createConstraint('eee/ddd', '^3.0', direct: true, dev: true),
            ],
        ]);
        static::assertSame(2, $graph->package('dee/bee')->distance);
    }
}
