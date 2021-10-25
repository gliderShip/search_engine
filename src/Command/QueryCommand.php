<?php

namespace App\Command;

use App\Exception\BadExpressionException;
use App\Exception\ConsoleException;
use App\Service\Compiler;
use App\Service\DocumentManager;
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


    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $query = $input->getArgument('expression');
        $strQuery = implode(' ', $query);
        $this->logger->debug('Query: ', ['query' => $query, 'str_query' => $strQuery]);

        try {
            $documentCollection = $this->compiler->execute($strQuery);

            $this->logger->debug(__METHOD__ . '  Result', ["result" => $documentCollection]);
            $scoredDocuments = $documentCollection->getContent();
            $documentIds = array_keys($scoredDocuments);
            $document_list = implode(' ', $documentIds);
            $io->writeln("query results " . $document_list);

        } catch (ConsoleException $e) {
            $io->writeln($e->getMessage());
        }

        return self::CONSOLE_SUCCESS;
    }


}
