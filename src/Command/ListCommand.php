<?php

namespace App\Command;

use App\Service\RedisManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListCommand extends ConsoleCommand
{
    protected static $defaultName = 'show';
    protected static $defaultDescription = 'Add a short description for your command';

    /**
     * @var RedisManager
     */
    private $redisManager;


    public function __construct(string $name = null, RedisManager $documentManager)
    {
        parent::__construct($name);

        $this->redisManager = $documentManager;
    }


    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $allKeys = $this->redisManager->getRedis()->keys('*');
        $documents = $this->redisManager->getRedis()->keys(RedisManager::DOCUMENTS_KEY . '*');
        $tokens = $this->redisManager->getRedis()->keys(RedisManager::TOKENS_KEY . '*');

        $io->note('Documents');
        $io->writeln($documents);

        $io->note('Tokens');
        $io->writeln($tokens);

        $io->note('Keys');
        $io->writeln($allKeys);

        $io->title('Document Details');
        foreach ($documents as $document) {
            $io->note("Document ID -> $document");
            $documentContent = $this->redisManager->getRedis()->zrange($document, 0, -1);
            $io->writeln($documentContent);

        }

        $io->title('Token Details');
        foreach ($tokens as $token) {
            $io->note("Token ID -> $token");
            $tokenContent = $this->redisManager->getRedis()->zrange($token, 0, -1);
            $io->writeln($tokenContent);

        }


        return self::CONSOLE_SUCCESS;
    }

}
