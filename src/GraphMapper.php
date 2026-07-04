<?php

declare(strict_types=1);


namespace DTL\Depgraph;

use DTL\Depgraph\Graph\Graph;

interface GraphMapper
{
    public function map(Graph $graph): void;
}
