<?php

declare(strict_types=1);


namespace DTL\Depgraph\Composer\Mapper;

use Composer\Package\Locker;
use Composer\Repository\RepositorySet;
use DTL\Depgraph\GraphMapper;
use DTL\Depgraph\Graph\Graph;
use Override;
use Psr\Log\LoggerInterface;

final class AuditMapper implements GraphMapper
{
    public function __construct(
        private RepositorySet $repositorySet,
        private Locker $locker,
        private LoggerInterface $logger,
    )
    {
    }

    #[Override]
    public function map(Graph $graph): void
    {
        $this->logger->info('Auditing');
        $advisories = $this->repositorySet->getMatchingSecurityAdvisories(
            $this->locker->getLockedRepository()->getPackages(),
            false,
        );
        foreach ($advisories['advisories'] as $packageName => $advisories) {
            foreach ($advisories as $advisory) {
                if ($advisory->cve === null) {
                    continue;
                }
                $graph->package($packageName)->cves[] = $advisory->cve;
            }
        }
    }
}
