<?php

declare(strict_types=1);


namespace DTL\Depgraph\Composer;


use Composer\Package\CompletePackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Repository\RepositoryInterface;
use DTL\Depgraph\GraphPhactory;
use DTL\Depgraph\Graph\Constraint;
use DTL\Depgraph\Graph\Graph;
use DTL\Depgraph\Graph\Package;
use Override;

final class ComposerGraphFactory implements GraphPhactory
{
    public function __construct(
        private RootPackageInterface $rootPackage,
        private RepositoryInterface $repository
    )
    {
    }

    #[Override]
    public function getGraph(): Graph
    {
        $packages = [];
        $constraints = [];

        // map all the other packages
        $composerPackages = $this->repository->getPackages();
        foreach ($composerPackages as $composerPackage) {
            $isRoot = $this->rootPackage->getName() === $composerPackage->getName();

            if (!$composerPackage instanceof CompletePackageInterface) {
                throw new \RuntimeException(sprintf(
                    'Expected complete package got: %s',
                    get_debug_type($composerPackage)
                ));
            }

            $packages[$composerPackage->getName()] = new Package(
                $composerPackage->getName(),
                $composerPackage->getPrettyVersion(),
                abandonned: $composerPackage->isAbandoned(),
                licence: array_values($composerPackage->getLicense()),
            );

            $constraints[$composerPackage->getName()] = [];

            foreach ($composerPackage->getRequires() as $link) {
                $constraints[$composerPackage->getName()][] = Constraint::fromLink(
                    $link,
                    // mark all non-direct packages as dev as we'll need to
                    // traverse the graph later to determine which ones are
                    // production (and therefore which are only dev).
                    $isRoot ? false : true,
                    $isRoot,
                );
            }

            if ($isRoot) {
                foreach ($composerPackage->getDevRequires() as $link) {
                    $constraints[$composerPackage->getName()][] = Constraint::fromLink(
                        $link,
                        true,
                        true,
                    );
                }
            }
        }

        return Graph::new(
            array_values($packages),
            $constraints,
        );
    }
}
