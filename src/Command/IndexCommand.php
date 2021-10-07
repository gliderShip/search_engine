<?php

namespace App\Command;

use App\Exception\ConsoleArgumentException;
use App\Exception\IndexException;
use App\Service\DocumentManager;
use App\Validator\IndexArgumentsValidator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IndexCommand extends ConsoleCommand
{
    protected static $defaultName = 'index';
    protected static $defaultDescription = 'Add a short description for your command';

    /**
     * @var IndexArgumentsValidator
     */
    private $indexArgumentsValidator;

    /**
     * @var integer $documentId
     */
    private $documentId;

    /**
     * @var array $tokens
     */
    private $tokens;

    /**
     * @var DocumentManager
     */
    private $documentManager;


    public function __construct(string $name, IndexArgumentsValidator $indexArgumentsValidator, DocumentManager $documentManager)
    {
        parent::__construct($name);
        $this->indexArgumentsValidator = $indexArgumentsValidator;
        $this->documentManager = $documentManager;
    }


    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('docId', InputArgument::REQUIRED, 'Document ID', $default = null)
            ->addArgument('tokens', InputArgument::IS_ARRAY, 'The document tokens');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->documentId = $input->getArgument('docId');
        $this->tokens = $input->getArgument('tokens');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->indexArgumentsValidator->validate($this->documentId, $this->tokens);
        } catch (ConsoleArgumentException $e) {
            $io->write($e->getMessage());
            return self::CONSOLE_ARGUMENT_ERROR;
        } catch (IndexException $e) {
            $io->write($e->getMessage());
            return self::INDEX_ERROR;
        }

        $this->documentManager->upsert($this->documentId, $this->tokens);


        if ($this->tokens) {
            $io->note(sprintf('You passed an argument: %s', $this->tokens));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return 0;
    }
}
