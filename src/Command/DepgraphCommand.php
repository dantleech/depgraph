<?php

declare(strict_types=1);


namespace DTL\Depgraph\Command;

use Composer\Command\BaseCommand;
use DTL\Depgraph\DepgraphContainerFactory;
use Override;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DepgraphCommand extends BaseCommand
{
    /**
     * @var DepgraphContainerFactory
     */
    private $containerFactory;

    public function __construct(
        DepgraphContainerFactory $containerFactory
    ) {
        parent::__construct();
        $this->containerFactory = $containerFactory;
    }

    #[Override]
    protected function configure(): void
    {
        $this->setName('depgraph');
        $this->setDescription('Show ur depgraph');
    }

    #[Override]
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }
}
