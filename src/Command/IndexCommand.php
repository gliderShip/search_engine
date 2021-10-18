<?php

namespace App\Command;

use App\DTO\DocumentDto;
use App\Exception\ConsoleArgumentException;
use App\Exception\IndexException;
use App\Service\DocumentManager;
use App\Validator\IndexArgumentsValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class IndexCommand extends ConsoleCommand
{
    protected static $defaultName = 'index';
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

    /**
     * @var DocumentDto
     */
    private $documentDto;

    private LoggerInterface $logger;


    public function __construct(string $name = null, ValidatorInterface $validator, IndexArgumentsValidator $indexArgumentsValidator, DocumentManager $documentManager, LoggerInterface $logger)
    {
        parent::__construct($name);
        $this->validator = $validator;
        $this->indexArgumentsValidator = $indexArgumentsValidator;
        $this->documentManager = $documentManager;
        $this->logger = $logger;
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

        $documentId = $input->getArgument('docId');
        $tokens = $input->getArgument('tokens');
        $this->documentDto = new DocumentDto($documentId, $tokens);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->indexArgumentsValidator->validate($this->documentDto);
        } catch (IndexException $e) {
            $io->write($e->getMessage());
            return self::INDEX_ERROR;
        }

        $response = $this->documentManager->upsert($this->documentDto);

        $keys = $this->documentManager->getStorageManager()->findAll();
        $this->logger->debug(__METHOD__." Keys", $keys);
        foreach ($keys as $key) {
            dump($key);
            $this->logger->debug("Key $key", ['content' => $this->documentManager->getStorageManager()->getEntityById($key)]);
        }

        $io->write('index ok ' . $this->documentDto->getId());

        return self::CONSOLE_SUCCESS;
    }
}
