<?php

declare(strict_types=1);


namespace DTL\Depgraph\Tests\Unit\Renderer;

use DTL\Depgraph\Graph\Constraint;
use DTL\Depgraph\Graph\Dependency;
use DTL\Depgraph\Graph\Graph;
use DTL\Depgraph\Graph\Package;
use DTL\Depgraph\Renderer\DotRenderer;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class DotRendererTest extends TestCase
{
    #[DataProvider('provideRender')]
    public function testRender(string $expectationFilename, Graph $graph): void
    {
        $path = __DIR__ . sprintf('/expectation/dot/%s', $expectationFilename);
        $rendered = (new DotRenderer())->render($graph);

        if (!file_exists($path)) {
            (new Filesystem())->dumpFile($path, $rendered);
            static::markTestSkipped('Generated');
        }
        $contents = trim((string)file_get_contents($path));

        static::assertEquals($rendered, $contents);

    }
    /**
     * @return Generator<array{string,Graph}>
     */
    public static function provideRender(): Generator
    {
        yield [
            'root',
            new Graph([
                new Package('foobar/barfoo', '1.0.0', []),
            ], []),
        ];

        yield [
            'requires',
            new Graph([
                new Package('acme/one', '1.0.0', [
                    new Constraint('acme/two', '^2.0.0', isDev: false),
                ]),
                new Package('acme/two', '2.0.0', []),
            ], [
            ]),
        ];
    }
}
