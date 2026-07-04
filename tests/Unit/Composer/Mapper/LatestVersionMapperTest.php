<?php

declare(strict_types=1);


namespace DTL\Depgraph\Tests\Unit\Composer\Mapper;

use Composer\Package\Version\VersionSelector;
use DTL\Depgraph\Composer\Mapper\LatestVersionMapper;
use DTL\Depgraph\Graph\Graph;
use DTL\Depgraph\Tests\Unit\TestCase;
use Psr\Log\LoggerInterface;

final class LatestVersionMapperTest extends TestCase
{
    public function testVersionFinderException(): void
    {
        $graph = Graph::new([
            self::createPackage('daniel/simple', version: 'dev-main', licence: []),
            self::createPackage('barfoo/foobar', version: 'dev-main', licence: []),
        ], [
            'daniel/simple' => [
                self::createConstraint('barfoo/foobar'),
            ],
        ]);
        
        $selector = $this->createStub(VersionSelector::class);
        $selector->method('findBestCandidate')->willThrowException(new \RuntimeException('No sorry'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(1))->method('warning')->with('No sorry');
        
        (new LatestVersionMapper(
            $selector,
            $logger
        ))->map($graph);
    }
}
