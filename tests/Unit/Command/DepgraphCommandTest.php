<?php

declare(strict_types=1);


namespace DTL\Depgraph\Tests\Unit\Command;

use Closure;
use DTL\Depgraph\Command\DepgraphCommand;

use DTL\Depgraph\DepgraphFactory;
use DTL\Depgraph\Tests\Support\TestLogger;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Tester\CommandTester;

final class DepgraphCommandTest extends TestCase
{
    /**
     * @param Closure(CommandTester,TestLogger): void $test
     */
    #[DataProvider('provideCommand')]
    public function testCommand(Closure $test): void
    {
        $logger = new TestLogger();
        $tester = $this->createCommand($logger);
        $test($tester, $logger);
    }
    /**
     * @return Generator<string,array{Closure(CommandTester,TestLogger): void}>
     */
    public static function provideCommand(): Generator
    {
        yield 'no arguments' => [
            static function (CommandTester $tester) {
                $result = $tester->run([]);
                self::assertEquals(0, $result->statusCode);
            },
        ];

        yield '--no-dev' => [
            static function (CommandTester $tester) {
                self::assertEquals(0, $tester->run([
                    '--no-dev' => true,
                ])->statusCode);
            },
        ];

        yield '--no-prod' => [
            static function (CommandTester $tester) {
                self::assertEquals(0, $tester->run([
                    '--no-prod' => true,
                ])->statusCode);
            },
        ];

        yield '--max-depth' => [
            static function (CommandTester $tester) {
                self::assertEquals(0, $tester->run([
                    '--max-depth' => 1,
                ])->statusCode);
            },
        ];

        yield '--outdated' => [
            static function (CommandTester $tester, TestLogger $logger) {
                self::assertEquals(0, $tester->run([
                    '--outdated' => true,
                ])->statusCode);
                self::assertStringContainsString(
                    'Finding latest versions',
                    $logger->containingMessage('Finding')->at(0)->message
                );
            },
        ];

        yield '--audit' => [
            static function (CommandTester $tester, TestLogger $logger) {
                self::assertEquals(0, $tester->run([
                    '--audit' => true,
                ])->statusCode);
                self::assertStringContainsString(
                    'Auditing',
                    $logger->containingMessage('Auditing')->at(0)->message
                );
            },
        ];
    }

    private function createCommand(LoggerInterface $logger): CommandTester
    {
        return new CommandTester(
            new DepgraphCommand(
                DepgraphFactory::default(offline: true, logger: $logger),
            )
        );
    }
}
