<?php

declare(strict_types=1);


namespace DTL\Depgraph\Tests\Unit\Graph;

use DTL\Depgraph\Graph\Graph;
use DTL\Depgraph\Tests\Unit\TestCase;

final class GraphTest extends TestCase
{
    public function testNoDev(): void
    {
        $graph = Graph::new([
            self::createPackage('prod'),
            self::createPackage('dev'),
            self::createPackage('not-dev'),
        ], [
            'prod' => [
                self::createConstraint('dev', dev: true),
                self::createConstraint('not-dev', dev: false),
            ],
        ]);

        static::assertEquals(Graph::new([
            $this->createPackage('prod'),
            $this->createPackage('not-dev'),
        ], [
            'prod' => [
                self::createConstraint('not-dev', dev: false),
            ],
        ]), $graph->noDev());
    }

    public function testNoProd(): void
    {
        $graph = Graph::new([
            self::createPackage('prod'),
            self::createPackage('dev'),
            self::createPackage('bar'),
        ], [
            'prod' => [
                self::createConstraint('dev', dev: true),
                self::createConstraint('bar', dev: false),
            ],
            'dev' => [
            ],
        ]);
        static::assertEquals(Graph::new([
            self::createPackage('prod'),
            self::createPackage('dev'),
        ], [
            'prod' => [
                self::createConstraint('dev', dev: true),
            ],
            'dev' => [
            ],
        ]), $graph->noProd());
    }

    public function testLevels(): void
    {
        $graph = Graph::new([
            self::createPackage('one'),
            self::createPackage('two'),
            self::createPackage('three'),
            self::createPackage('four'),
        ], [
            'one' => [
                self::createConstraint('two'),
                self::createConstraint('three'),
            ],
            'two' => [
                self::createConstraint('four'),
                self::createConstraint('three'),
            ],
            'three' => [
                self::createConstraint('four'),
            ],
            'four' => [],
        ]);
        static::assertSame(0, $graph->package('one')->distance);
        static::assertSame(1, $graph->package('two')->distance);
        static::assertSame(1, $graph->package('three')->distance);
        static::assertSame(2, $graph->package('four')->distance);
    }

    public function testShortestLevel(): void
    {
        $factory = static fn (array $oneDeps) => Graph::new([
                self::createPackage('one'),
                self::createPackage('two'),
                self::createPackage('three'),
                self::createPackage('four'),
            ], [
                // @mago-expect analyzer:possibly-invalid-argument
                'one' => $oneDeps,
                'two' => [
                    self::createConstraint('three'),
                ],
                'three' => [
                    self::createConstraint('four'),
                ],
                'four' => [],
            ]);
        
        $graph = $factory([
            self::createConstraint('four'),
            self::createConstraint('two'),
        ]);

        static::assertSame(1, $graph->package('two')->distance);
        static::assertSame(2, $graph->package('three')->distance);
        static::assertSame(1, $graph->package('four')->distance);
        
        $graph = $factory([
            self::createConstraint('two'),
            self::createConstraint('four'),
        ]);

        static::assertSame(1, $graph->package('two')->distance);
        static::assertSame(2, $graph->package('three')->distance);
        static::assertSame(1, $graph->package('four')->distance);
    }

    public function testLimitDepth(): void
    {
        $graph = Graph::new([
            self::createPackage('one'),
            self::createPackage('two'),
            self::createPackage('three'),
            self::createPackage('four'),
        ], [
            'one' => [
                self::createConstraint('two'),
                self::createConstraint('three'),
            ],
            'two' => [
                self::createConstraint('four'),
                self::createConstraint('three'),
            ],
            'three' => [
                self::createConstraint('four'),
            ],
            'four' => [],
        ]);

        $graph = $graph->limitDepth(1);

        static::assertTrue($graph->hasPackage('two'));
        static::assertTrue($graph->hasPackage('three'));
        static::assertFalse($graph->hasPackage('four'));
    }

    public function testPreserveUnregisteredPackages(): void
    {
        $graph = Graph::new([
            self::createPackage('one'),
        ], [
            'one' => [
                self::createConstraint('two'),
                self::createConstraint('three'),
            ],
        ]);
        $expected = Graph::new([
            self::createPackage('one'),
            self::createPackage('two', '?'),
            self::createPackage('three', '?'),
        ], [
            'one' => [
                self::createConstraint('two'),
                self::createConstraint('three'),
            ],
        ]);

        static::assertEquals($expected, $graph);
    }

    public function testCycleHandling(): void
    {
        $graph = Graph::new([
            self::createPackage('phpstan/phpstan-src'),
            self::createPackage('hoa/compiler'),
            self::createPackage('hoa/consistency'),
            self::createPackage('hoa/exception'),
            self::createPackage('hoa/event'),
        ], [
            'phpstan/phpstan-src' => [
                self::createConstraint('hoa/compiler'),
            ],
            'hoa/compiler' => [
                self::createConstraint('hoa/consistency'),
                self::createConstraint('hoa/exception'),
                self::createConstraint('hoa/event'),
            ],
            'hoa/event' => [
                self::createConstraint('hoa/consistency'),
                self::createConstraint('hoa/exception'),
            ],
            'hoa/exception' => [
                self::createConstraint('hoa/consistency'),
            ],
            'hoa/consistency' => [
                self::createConstraint('hoa/exception'),
            ],
        ]);

        static::assertSame('hoa/exception', $graph->package('hoa/exception')->name);
    }
}
