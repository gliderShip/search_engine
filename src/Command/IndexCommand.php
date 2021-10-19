<?php

namespace App\Command;

use App\DTO\DocumentDto;
use App\Exception\ConsoleArgumentException;
use App\Exception\IndexException;
use App\Service\DocumentManager;
use App\Service\RedisManager;
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

    private ValidatorInterface $validator;

    private IndexArgumentsValidator $indexArgumentsValidator;

    private DocumentManager $documentManager;

    private DocumentDto $documentDto;

    private LoggerInterface $logger;

    /**
     * Remove after debugging
     */
    private RedisManager $redisManager;


    public function __construct(string $name = null, ValidatorInterface $validator, IndexArgumentsValidator $indexArgumentsValidator, DocumentManager $documentManager, LoggerInterface $logger, RedisManager $redisManager)
    {
        parent::__construct($name);
        $this->validator = $validator;
        $this->indexArgumentsValidator = $indexArgumentsValidator;
        $this->documentManager = $documentManager;
        $this->logger = $logger;
        $this->redisManager = $redisManager;
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

        $document = $this->documentManager->upsert($this->documentDto);

        $this->logger->debug(__METHOD__." Document ".$document->getId(), ['redis ID' => $document->getDbId(), 'content' => $this->redisManager->getSortedSetById($document->getDbId())]);

        $io->write('index ok ' . $document->getId());

        return self::CONSOLE_SUCCESS;
    }
}
