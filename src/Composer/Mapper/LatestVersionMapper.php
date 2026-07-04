<?php

declare(strict_types=1);


namespace DTL\Depgraph\Composer\Mapper;


use Composer\Package\Version\VersionSelector;
use DTL\Depgraph\GraphMapper;
use DTL\Depgraph\Graph\Graph;
use Override;
use Psr\Log\LoggerInterface;

final class LatestVersionMapper implements GraphMapper
{
    public function __construct(
        private VersionSelector $versionSelector,
        private LoggerInterface $logger,
    )
    {
    }

    #[Override]
    public function map(Graph $graph): void
    {
        $this->logger->info('Finding latest versions');
        foreach ($graph->packages as $package) {
            if ($package === $graph->rootPackage) {
                continue;
            }

            $this->logger->debug(sprintf('Finding best version for: %s', $package->name));

            try {
                $best = $this->versionSelector->findBestCandidate($package->name);
            } catch (\Exception $e) {
                $this->logger->warning($e->getMessage());
                continue;
            }

            if ($best) {
                $package->latestVersion = $best->getPrettyVersion();
            }
        }
    }
}
