<?php

namespace ConsoleConfigResolver\Test\Fixtures;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand
{
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('command')
            ->setDescription('description')
            ->setHelp('help');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return 0;
    }
}