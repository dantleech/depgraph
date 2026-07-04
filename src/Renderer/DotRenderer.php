<?php

declare(strict_types=1);

namespace DTL\Depgraph\Renderer;

use DTL\Depgraph\Graph\Graph;
use DTL\Depgraph\Graph\Package;
use DTL\Depgraph\Renderer;
use Override;

final class DotRenderer implements Renderer
{
    #[Override]
    public function render(Graph $graph): string
    {
        $dot = ['digraph {'];
        $dependencies = [];
        foreach ($graph->packages as $package) {
            $dot[] = $this->renderPackage($package);
        }

        foreach ($graph->constraints as $from => $constraints) {
            foreach ($constraints as $constraint) {
                $dot[] = sprintf(
                    '"%s" -> "%s" [label="%s", style="%s", penwidth=%s]',
                    $from,
                    $constraint->package,
                    $constraint->version,
                    $constraint->dev ? 'dashed' : 'solid',
                    $constraint->direct === true ? '2' : '1',
                );
            }
        }

        $dot[] = '}';

        return implode("\n", $dot);
    }

    private function renderPackage(Package $package): string
    {
        $attrs = [];
        $attrs['label'] = (static function () use ($package) {
            $label = [];
            $label[] = sprintf('%s +%d', $package->name, $package->distance);
            $label[] = sprintf('@ %s', $package->version);
            if ($package->latestVersion !== null) {
                $latestVersionString = $package->latestVersion;
                if ($package->isOutdated()) {
                    $latestVersionString = sprintf('<B>%s</B>', $latestVersionString);
                }

                $label[] = sprintf('= %s', $latestVersionString);
            }
            if ($package->licence) {
                $label[] = implode(', ', $package->licence);
            }
            if (count($package->cves) > 0) {
                $label[] = sprintf('<B>%d CVE(s)</B>', count($package->cves));
            }
            if ($package->abandonned) {
                $label[] = sprintf('<B>ABANDONNED</B>', count($package->cves));
            }
            return implode("<BR/>", $label);
        })();

        if ($package->isOutdated()) {
            $attrs['style'] = 'filled';
            $attrs['fillcolor'] = 'yellow';
            $attrs['fontcolor'] = 'black';
        }
        if ($package->abandonned) {
            $attrs['style'] = 'filled';
            $attrs['fillcolor'] = 'orange';
            $attrs['fontcolor'] = 'black';
        }

        if ($package->isInsecure()) {
            $attrs['style'] = 'filled';
            $attrs['fillcolor'] = 'darkred';
            $attrs['fontcolor'] = 'white';
        }

        $dot = [
            sprintf(
                "\"%s\" [%s]",
                $package->name,
                implode(', ', array_map(static function (string $key, string $value) {
                    if ($key === 'label') {
                        return sprintf('%s=<%s>', $key, $value);
                    }
                    return sprintf('%s="%s"', $key, $value);
                }, array_keys($attrs), array_values($attrs)))
            ),
        ];

        return implode("\n", $dot);
    }
}
