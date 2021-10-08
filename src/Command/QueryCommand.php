<?php

namespace App\Command;

use App\Service\DocumentManager;
use App\Validator\IndexArgumentsValidator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class QueryCommand extends ConsoleCommand
{
    protected static $defaultName = 'query';
    protected static $defaultDescription = 'Add a short description for your command';
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var IndexArgumentsValidator
     */
    private $indexArgumentsValidator;

    /**
     * @var DocumentManager
     */
    private $documentManager;


    public function __construct(string $name = null, ValidatorInterface $validator, IndexArgumentsValidator $indexArgumentsValidator, DocumentManager $documentManager)
    {
        parent::__construct($name);
        $this->validator = $validator;
        $this->indexArgumentsValidator = $indexArgumentsValidator;
        $this->documentManager = $documentManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('expression', InputArgument::IS_ARRAY, 'The query expression', $default = null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $query = $input->getArgument('expression');
        dump($query);
        $documents = $this->documentManager->findByTokens($query);
        $documents = $this->documentManager->findByToken($query);
        dump($documents);
//        dd('the end');
//
//
//        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return 0;
    }
}
