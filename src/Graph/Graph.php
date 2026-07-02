<?php

namespace DTL\Depgraph\Graph;

final class Graph
{
    /**
     * @param list<Package> $packages
     * @param list<Constraint> $constraints
     */
    public function __construct(public array $packages, public array $constraints)
    {
    }
}
