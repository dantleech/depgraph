<?php

declare(strict_types=1);


namespace DTL\Depgraph;



class DepgraphContainerFactory
{
    public function create(): DepgraphContainer
    {
        return new DepgraphContainer(
        );
    }
}
