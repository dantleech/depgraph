<?php

declare(strict_types=1);


namespace DTL\Depgraph;

use DTL\Depgraph\Graph\Graph;

interface GraphPhactory
{
    public function getGraph(): Graph;
}
