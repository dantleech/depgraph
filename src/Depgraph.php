<?php

declare(strict_types=1);

namespace DTL\Depgraph;

use DTL\Depgraph\Composer\Mapper\AuditMapper;

use DTL\Depgraph\Composer\Mapper\LatestVersionMapper;
use Psr\Log\LoggerInterface;

final class Depgraph
{
    public function __construct(
        private GraphPhactory $factory,
        private Renderer $renderer,
        private LatestVersionMapper $latestVersions,
        private AuditMapper $auditMapper,
        private LoggerInterface $logger,
    )
    {
    }

    public function render(GraphOptions $graphOptions): string
    {
        $this->logger->info('Building dependency graph');
        $graph = $this->factory->getGraph();

        if ($graphOptions->noDev) {
            $graph = $graph->noDev();
        }
        if ($graphOptions->noProd) {
            $graph = $graph->noProd();
        }
        if ($graphOptions->maxDepth) {
            $graph = $graph->limitDepth($graphOptions->maxDepth);
        }
        if ($graphOptions->outdated) {
            $this->latestVersions->map($graph);
        }
        if ($graphOptions->audit) {
            $this->auditMapper->map($graph);
        }
        return $this->renderer->render($graph);
    }
}
