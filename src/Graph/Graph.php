<?php

declare(strict_types=1);


namespace DTL\Depgraph\Graph;

use Closure;

final class Graph
{
    private array $dependencies = [];

    /**
     * @param array<string,Package> $packages
     * @param array<string,list<Constraint>> $constraints
     */
    private function __construct(
        public readonly Package $rootPackage,
        public array $packages,
        public array $constraints
    )
    {
        foreach ($constraints as $packageName => $packageConstraints) {
            foreach ($packageConstraints as $constraint) {
                $this->dependencies[$constraint->package] = true;
            }
        }
    }

    /**
     * @param list<Package> $packages
     * @param array<string,list<Constraint>> $constraints
     */
    public static function new(array $packages, array $constraints = []): self
    {
        $packages = array_combine(
            array_map(static fn (Package $package) => $package->name, $packages),
            array_values($packages),
        );

        $dependencies = array_keys(array_reduce($constraints, static function ($deps, array $constraints) {
            foreach ($constraints as $constraint) {
                $deps[$constraint->package] = true;
            }
            return $deps;
        }, []));

        $diff = array_diff(array_keys($packages), $dependencies);

        if (count($diff) === 0) {
            throw new \RuntimeException(
                'No root package can be determined',
            );
        }

        if (count($diff) > 1) {
            throw new \RuntimeException(sprintf(
                'More than one root node in dependency tree: "%s" - this can mean that the composer.json file is out of sync with the lock file',
                implode('", "', $diff)
            ));
        }

        $rootPackage = $packages[reset($diff)] ?? null;

        // fill-in packages that are not in the lock file
        foreach (array_diff($dependencies, array_keys($packages)) as $unregisteredPackage) {
            $packages[$unregisteredPackage] = new Package(
                $unregisteredPackage,
                '?'
            );
        }

        if (null === $rootPackage) {
            throw new \RuntimeException(
                'Root package not present in packages'
            );
        }

        $graph = new self($rootPackage, $packages, $constraints);
        $graph->mapDistances();
        $graph->mapDevDeps();

        return $graph;
    }

    private function mapDistances(): void
    {
        $this->visit(static function (Package $package, ?Package $_package, ?Constraint $_constraint, int $distance):  void {
            if ($distance < $package->distance) {
                $package->distance = $distance;
            }
        });
    }

    private function mapDevDeps(): void
    {
        $this->visit(static function (Package $package, ?Package $parent, ?Constraint $constraint, int $_distance):  void {
            if ($constraint && $parent?->dev === false) {
                $constraint->dev = false;
            }
            if ($constraint?->dev === false) {
                $package->dev = false;
            }
        });
    }

    /**
     * @param Closure(Package,?Package,?Constraint,int): void $visitor
     */
    private function visit(Closure $visitor): void
    {
        $this->descend($this->rootPackage, null, null, $visitor);
    }

    /**
     * @param Closure(Package,?Package,?Constraint,int): void $visitor
     */
    private function descend(
        Package $package,
        ?Package $parentPackage,
        ?Constraint $constraint,
        Closure $visitor,
        int $distance = 0,
        array $breadcrumb = [],
    ): void {
        $visitor($package, $parentPackage, $constraint, $distance);

        // this package has already been visited
        if (in_array($package->name, $breadcrumb, strict: true)) {
            return;
        }

        $breadcrumb[] = $package->name;
        foreach ($this->constraints[$package->name] ?? [] as $constraint) {
            if (!array_key_exists($constraint->package, $this->packages)) {
                continue;
            }
            $dependency = $this->packages[$constraint->package];
            $this->descend($dependency, $package, $constraint, $visitor, $distance + 1, $breadcrumb);
        }
    }

    public function package(string $name): Package
    {
        if (!array_key_exists($name, $this->packages)) {
            throw new \RuntimeException(sprintf(
                'Package "%s" not existing, existing packages: "%s"',
                $name,
                implode('", "', array_keys($this->packages))
            ));
        }

        return $this->packages[$name];
    }

    public function hasPackage(string $package): bool
    {
        return array_key_exists($package, $this->packages);
    }

    public function noDev(): self
    {
        return $this->filterConstraints(
            static fn (Constraint $c) => $c->dev === false
        )->clean();
    }

    public function noProd(): self
    {
        return $this->filterConstraints(
            static fn (Constraint $c) => $c->dev === true
        )->clean();
    }

    public function clean(): self
    {
        return $this
            ->filterPackages(fn (Package $p) => array_key_exists($p->name, $this->constraints) || array_key_exists($p->name, $this->dependencies))
            ->filterConstraints(fn (Constraint $c) => $this->hasPackage($c->package));
    }

    /**
     * @param Closure(Package):bool $closure
     */
    public function filterPackages(Closure $closure): self
    {
        return new self(
            $this->rootPackage,
            array_filter($this->packages, $closure),
            $this->constraints
        );
    }

    /**
     * @param Closure(Constraint):bool $closure
     */
    public function filterConstraints(Closure $closure): self
    {
        $newConstraints = $this->constraints;
        foreach ($newConstraints as $name => $constraints) {
            foreach ($constraints as $index => $constraint) {
                if (true === $closure($constraint)) {
                    continue;
                }
                unset($newConstraints[$name][$index]);
                if ($newConstraints[$name] === []) {
                    unset($newConstraints[$name]);
                }
            }
        }

        return new self(
            $this->rootPackage,
            $this->packages,
            array_map(static function (array $constraints) {return array_values($constraints);}, $newConstraints)
        );
    }

    public function limitDepth(int $level): self
    {
        return $this
            ->filterPackages(static fn (Package $package) => $package->distance <= $level)
            ->clean();
    }
}
