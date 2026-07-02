<?php

declare(strict_types=1);


namespace DTL\Depgraph;

use DTL\Depgraph\Adapter\Github\GithubChangelogFactory;
use DTL\Depgraph\Model\ChangelogFactory;
use DTL\Depgraph\Model\ComposerLockArchiver;
use DTL\Depgraph\Model\Filesystem;
use DTL\Depgraph\Model\Filter;
use DTL\Depgraph\Model\HistoryCompiler;
use DTL\Depgraph\Model\PackageHistories;
use DTL\Depgraph\Adapter\Symfony\Report\ConsoleReport;

final class DepgraphContainer
{
    /**
     * @var string
     */
    private $lockFilePath;

    /**
     * @var string
     */
    private $compareLockFilePath;

    /**
     * @var string
     */
    private $cachePath;

    /**
     * @var string|null
     */
    private $githubOauthToken;

    /**
     * @var int|null
     */
    private $maxCommits;

    /**
     * @var int|null
     */
    private $maxRepos;

    public function __construct(
        string $lockFilePath,
        string $compareLockFilePath,
        string $cachePath,
        #[\SensitiveParameter] ?string $githubOauthToken,
        ?int $maxCommits,
        ?int $maxRepos
    ) {
        $this->lockFilePath = $lockFilePath;
        $this->compareLockFilePath = $compareLockFilePath;
        $this->cachePath = $cachePath;
        $this->githubOauthToken = $githubOauthToken;
        $this->maxCommits = $maxCommits;
        $this->maxRepos = $maxRepos;
    }

    public function histories(): PackageHistories
    {
        return (new HistoryCompiler(
            $this->filesystem(),
            $this->lockFilePath,
            $this->compareLockFilePath,
            $this->filter()
        ))->compile();
    }

    public function changelogFactory(): ChangelogFactory
    {
        return new GithubChangelogFactory($this->cachePath, $this->githubOauthToken, $this->maxCommits);
    }

    public function filesystem(): Filesystem
    {
        return new Filesystem();
    }

    public function archiver(): ComposerLockArchiver
    {
        return new ComposerLockArchiver(
            $this->filesystem(),
            $this->lockFilePath,
            $this->compareLockFilePath
        );
    }

    public function consoleReport(): ConsoleReport
    {
        return new ConsoleReport(
            $this->histories(),
            $this->changelogFactory(),
            $this->maxRepos
        );
    }

    private function filter(): Filter
    {
        return new Filter();
    }
}
