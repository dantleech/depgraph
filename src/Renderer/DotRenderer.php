<?php

declare(strict_types=1);

namespace DTL\Depgraph\Renderer;

use DTL\Depgraph\Graph\Graph;
use DTL\Depgraph\Graph\Package;

final class DotRenderer
{
    public function render(Graph $graph): string
    {
        $dot = ['digraph {'];
        $dependencies = [];
        foreach ($graph->packages as $package) {
            $dot[] = $this->renderPackage($package);
            $dependencies = array_merge($dependencies, [
                $package->name => $package->dependencies
            ]);
        }

        foreach ($dependencies as $package => $dependency) {
            foreach ($dependency as $constraint) {
                $dot[] = sprintf('"%s" -> "%s"', $package, $constraint->package);
            }
        }

        $dot[] = '}';

        return implode("\n", $dot);
    }

    private function renderPackage(Package $package): string
    {
        $dot = [
            sprintf('"%s"', $package->name),
        ];

        return implode("\n", $dot);
    }
}
