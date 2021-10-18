<?php

namespace App\Command;

use App\Service\DocumentManager;
use App\Service\Compiler;
use App\Validator\IndexArgumentsValidator;
use Psr\Log\LoggerInterface;
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
     * @var Compiler
     */
    private $compiler;

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
    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(string $name = null, Compiler $compiler, DocumentManager $documentManager, ValidatorInterface $validator, IndexArgumentsValidator $indexArgumentsValidator, LoggerInterface $logger)
    {
        parent::__construct($name);
        $this->compiler = $compiler;
        $this->validator = $validator;
        $this->indexArgumentsValidator = $indexArgumentsValidator;
        $this->documentManager = $documentManager;
        $this->logger = $logger;
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
        $strQuery = implode(' ', $query);
        $this->logger->debug('Query: ', ['query' => $query, 'str_query' => $strQuery]);

        $ast = $this->compiler->execute($strQuery);
        dump($ast);
//        $documents = $this->documentManager->getDocumentsContainingAny($query);
//        $documents = $this->documentManager->getDocumentsContainingAll($query);
//        $documents = $this->documentManager->findByToken($query);
//        dump($documents);
//        dd('the end');
//
//
//        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return 0;
    }


}
