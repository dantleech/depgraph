<?php

declare(strict_types=1);

namespace DTL\Depgraph\Graph;

final class Package
{
    /**
     * @param list<Constraint> $dependencies
     */
    public function __construct(
        public string $name,
        public string $version,
        public array $dependencies,
    )
    {
    }
}
