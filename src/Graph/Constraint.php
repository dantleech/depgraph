<?php

declare(strict_types=1);

namespace DTL\Depgraph\Graph;

final class Constraint
{
    public function __construct(
        public string $package,
        public string $version,
        public bool $isDev,
    )
    {
    }
}
