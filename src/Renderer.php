<?php

declare(strict_types=1);


namespace DTL\Depgraph;

use DTL\Depgraph\Graph\Graph;

interface Renderer
{
    public function render(Graph $graph): string;
}
