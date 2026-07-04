<?php

declare(strict_types=1);


namespace DTL\Depgraph\Tests\Unit\Renderer;


use DTL\Depgraph\Graph\Graph;
use DTL\Depgraph\Renderer\DotRenderer;
use DTL\Depgraph\Tests\Unit\TestCase;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Filesystem\Filesystem;

final class DotRendererTest extends TestCase
{
    #[DataProvider('provideRender')]
    public function testRender(string $expectationFilename, Graph $graph): void
    {
        $path = sprintf('%s/expectation/dot/%s', __DIR__, $expectationFilename);
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
            Graph::new([
                self::createPackage('foobar/barfoo', '1.0.0'),
            ], []),
        ];

        yield [
            'requires',
            Graph::new([
                self::createPackage('acme/one', '1.0.0'),
                self::createPackage('acme/two', '2.0.0'),
            ], [
                'acme/one' => [
                    self::createConstraint('acme/two', '^2.0.0')
                ],
            ]),
        ];
    }
}
