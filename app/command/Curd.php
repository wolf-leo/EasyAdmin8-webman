<?php

namespace app\command;

use app\common\services\curd\BuildCurd;
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
        $build = (new BuildCurd())->setTable($table);
        $build->render();
        $fileList = $build->getFileList();
        $output->writeln(">>>>>>>>>>>>>>>");
        $build->create();
        foreach ($fileList as $key => $val) {
            $output->writeln($key);
        }
        $output->writeln(">>>>>>>>>>>>>>>");
        $output->writeln('自动生成CURD成功');
        return self::SUCCESS;
    }

}