<?php

declare(strict_types=1);

namespace DTL\Depgraph\Graph;

use Composer\Package\Link;

final class Constraint
{
    public function __construct(
        public string $package,
        public string $version,
        public bool $dev,
        public bool $direct,
    )
    {
    }

    public static function fromLink(Link $link, bool $dev, bool $direct): self
    {
        return new self(
            $link->getTarget(),
            $link->getPrettyConstraint(),
            dev: $dev,
            direct: $direct,
        );
    }
}
