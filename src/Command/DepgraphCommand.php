<?php

declare(strict_types=1);


namespace DTL\Depgraph\Command;


use DTL\Depgraph\DepgraphFactory;
use Psl\Type;

use DTL\Depgraph\GraphOptions;

use Override;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DepgraphCommand extends Command
{
    const ARG_PATH = 'path';
    const OPT_NO_DEV = 'no-dev';
    const OPT_NO_PROD = 'no-prod';
    const OPT_MAX_DEPTH = 'max-depth';
    const OPT_OUTDATED = 'outdated';
    const OPT_AUDIT = 'audit';


    public function __construct(
        private DepgraphFactory $depgraph
    )
    {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->setName('depgraph');
        $this->setDescription('Show ur depgraph');
        $this->addArgument(self::ARG_PATH, InputArgument::OPTIONAL, 'Path to composer file', 'composer.json');
        $this->addOption(self::OPT_NO_DEV, null, InputOption::VALUE_NONE, 'Do not include development dependencies');
        $this->addOption(self::OPT_NO_PROD, null, InputOption::VALUE_NONE, 'Do not include production dependencies');
        $this->addOption(self::OPT_MAX_DEPTH, 'd', InputOption::VALUE_REQUIRED, 'Limit graph to given depth');
        $this->addOption(self::OPT_OUTDATED, null, InputOption::VALUE_NONE, 'Check outdated dependencies');
        $this->addOption(self::OPT_AUDIT, null, InputOption::VALUE_NONE, 'Mark packages with security vulnerabilities');
    }

    #[Override]
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerPath = (string)$input->getArgument(self::ARG_PATH);
        $noDev = Type\bool()->coerce($input->getOption(self::OPT_NO_DEV));
        $noProd = Type\bool()->coerce($input->getOption(self::OPT_NO_PROD));
        $outdated = Type\bool()->coerce($input->getOption(self::OPT_OUTDATED));
        $audit = Type\bool()->coerce($input->getOption(self::OPT_AUDIT));
        $maxDepth = Type\nullable(Type\u8())->coerce($input->getOption(self::OPT_MAX_DEPTH));

        $output->writeln($this->depgraph->create($composerPath)->render(new GraphOptions(
            noDev: $noDev,
            noProd: $noProd,
            maxDepth: $maxDepth,
            outdated: $outdated,
            audit: $audit,
        )));

        return 0;
    }
}
