<?php

declare(strict_types=1);


namespace DTL\Depgraph;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\ConsoleIO;
use Composer\Package\Version\VersionSelector;
use Composer\Repository\ComposerRepository;
use Composer\Repository\RepositorySet;
use DTL\Depgraph\Composer\Mapper\AuditMapper;
use DTL\Depgraph\Composer\Mapper\LatestVersionMapper;
use DTL\Depgraph\Console\ConsoleLogger;
use DTL\Depgraph\Renderer\DotRenderer;
use DTL\Depgraph\Composer\ComposerGraphFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class DepgraphFactory
{
    /**
     * @param bool $offline Do not use remote resources (for testing)
     */
    private function __construct(
        private bool $offline,
        private OutputInterface $output,
        private ?LoggerInterface $logger,
    )
    {
    }

    public static function default(
        bool $offline = false,
        OutputInterface $output = new NullOutput(),
        ?LoggerInterface $logger = null,
    ): self
    {
        return new self($offline, $output, $logger);
    }

    public function create(string $composerPath = 'composer.json'): Depgraph
    {
        $composer = $this->createComposer($composerPath);

        $logger = $this->createLogger();
        $repositorySet = $this->createRepositorySet($composer);

        return new Depgraph(
            $this->createGraphFactory($composer),
            new DotRenderer(),
            new LatestVersionMapper(
                $this->createVersionSelector($repositorySet),
                $logger,
            ),
            new AuditMapper(
                $repositorySet,
                $composer->getLocker(),
                $logger,
            ),
            $logger,
        );
    }

    private function createLogger(): LoggerInterface
    {
        return $this->logger ?? new ConsoleLogger($this->output);
    }

    private function createVersionSelector(RepositorySet $set): VersionSelector
    {
        return new VersionSelector($set);
    }


    public function createComposer(string $composerPath): Composer
    {
        return (new Factory())->createComposer(
            new ConsoleIO(new ArrayInput([]), new BufferedOutput(), new HelperSet()),
            $composerPath,
            disablePlugins: true,
            disableScripts: true,
        );
    }

    public function createGraphFactory(Composer $composer): GraphPhactory
    {
        $repository = (function (Composer $composer) {
            $locker = $composer->getLocker();
            if ($locker->isLocked()) {
                return $locker->getLockedRepository(true);
            }
            $this->createLogger()->warning('composer.lock not found - graph will be somewhat useless');
            return $composer->getRepositoryManager()->getLocalRepository();
        })($composer);
        $repository->addPackage($composer->getPackage());

        return new ComposerGraphFactory(
            $composer->getPackage(),
            $repository,
        );
    }

    public function createRepositorySet(Composer $composer): RepositorySet
    {
        $set = new RepositorySet();
        foreach ($composer->getRepositoryManager()->getRepositories() as $repository) {
            if ($this->offline === true && $repository instanceof ComposerRepository) {
                continue;
            }
            $set->addRepository($repository);
        }
        return $set;
    }
}
