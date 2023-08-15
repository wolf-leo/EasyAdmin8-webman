<?php

namespace app\command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Curd extends Command
{

    protected function configure()
    {
        $this->setName('curd')->addOption('table', 't', InputOption::VALUE_REQUIRED, '数据库表名');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = $input->getOption('table');
        $output->writeln('>>>>>>>>>>>>>>>');
        $output->writeln($table);
        return self::SUCCESS;
    }
    
}