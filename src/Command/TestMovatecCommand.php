<?php

namespace App\Command;

use App\Service\Movatec;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestMovatecCommand extends Command
{
    protected static $defaultName = 'app:test-movatec';
    protected static $defaultDescription = 'Add a short description for your command';

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');
        $movatec=new Movatec();


        $io->note(sprintf('Enviando a movatec'));
        $token=$movatec->login();
        $io->note(sprintf($token));
        $respuesta=$movatec->create_leads($token,269190,"","2025-05-15","+56995613576");

        

        $io->note(sprintf('Enviando a movatec: %s', $respuesta));
        

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return 0;
    }
}
