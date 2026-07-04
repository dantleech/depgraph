<?php

declare(strict_types=1);

namespace DTL\Depgraph\Graph;

use Composer\Package\RootPackageInterface;

final class Package
{
    /**
     * @param list<string> $cves
     * @param list<string> $licence
     */
    public function __construct(
        public string $name,
        public string $version,
        public int $distance = PHP_INT_MAX,
        public ?string $latestVersion = null,
        public array $cves = [],
        public bool $abandonned = false,
        public bool $dev = true,
        public array $licence = [],
    )
    {
    }

    public static function fromComposerPackage(RootPackageInterface $rootPackage): self
    {
        return new self(
            $rootPackage->getName(),
            $rootPackage->getVersion(),
        );
    }

    public function isOutdated(): bool
    {
        if (null === $this->latestVersion) {
            return false;
        }

        return $this->version !== $this->latestVersion;
    }

    public function isInsecure(): bool
    {
        if ([] === $this->cves) {
            return false;
        }

        return count($this->cves) > 0;

    }
}
