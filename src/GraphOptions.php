<?php

declare(strict_types=1);


namespace DTL\Depgraph;

final readonly class GraphOptions
{
    public function __construct(
        public bool $noDev = false,
        public bool $noProd = false,
        public ?int $maxDepth = null,
        public bool $outdated = false,
        public bool $audit = false,
    )
    {
    }
}

