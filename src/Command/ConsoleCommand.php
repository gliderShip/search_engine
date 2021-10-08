<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class ConsoleCommand extends Command
{
    public const CONSOLE_SUCCESS = 0;
    public const CONSOLE_ARGUMENT_ERROR = 1;
    public const INDEX_ERROR = 2;
}
