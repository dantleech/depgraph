<?php

declare(strict_types=1);


namespace DTL\Depgraph\Tests\Unit;

use DTL\Depgraph\Graph\Constraint;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use DTL\Depgraph\Graph\Package;

abstract class TestCase extends PHPUnitTestCase
{
    /**
     * @param list<string> $licence
     */
    protected  static function createPackage(string $name, string $version = '1.0.0', $licence = []): Package
    {
        return new Package(
            $name,
            $version,
            licence: $licence,
        );
    }
    
    protected static function createConstraint(string $name, string $version = '1.0', bool $dev = false, bool $direct = false): Constraint
    {
        return new Constraint($name, $version, $dev, $direct);
    }
}
