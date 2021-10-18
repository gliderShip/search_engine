<?php

namespace App\Service;

use App\DTO\DocumentDto;
use App\Model\StorageInterface;
use Predis\Client;
use Psr\Log\LoggerInterface;

class RedisManager implements StorageInterface
{
    public const DOCUMENTS_KEY = 'documents:';
    public const TOKENS_KEY = 'tokens:';

    private Client $redis;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->redis = new Client();
        $this->logger = $logger;
    }

    /**
     * @return Client
     */
    public function getRedis(): Client
    {
        return $this->redis;
    }

    public function findAll(): array
    {
        return $this->redis->keys('*');
    }


    public function upsert(DocumentDto $documentDto): string
    {
        $documentId = $documentDto->getId();
        $tokens = $documentDto->getTokens();

        $dbId = $this->getDocumentDbKey($documentId);

        $previousContent = $this->redis->zrange($dbId, 0, -1);
        $this->removeFromInvertedIndex($dbId, $previousContent);
        $this->redis->del($dbId);
        $this->redis->zadd($dbId, array_flip($tokens));
        $this->createInvertedIndex($dbId, $tokens);

        return $dbId;

    }

    public function getEntityById(string $dbId): array
    {
        return $this->redis->zrange($dbId, 0, -1);
    }

    public function getDocumentById(string $dbId): array
    {
        if (substr($dbId, 0, strlen(self::DOCUMENTS_KEY)) != self::DOCUMENTS_KEY) {
            throw new \BadMethodCallException("Invalid database id ->:$dbId for redis storage");
        }

        return $this->redis->zrange($dbId, 0, -1);
    }

    public function findByToken($token): string
    {
        $dbId = $this->getTokenDbKey($token);
        $this->logger->debug(__METHOD__." Documents Result $dbId");
        return $dbId;
    }

    public function getByToken($token): array
    {
        $dbId = $this->getTokenDbKey($token);
        $documents = $this->redis->zrevrange($dbId, 0, -1, ['withscores' => TRUE]);
        $this->logger->debug(__METHOD__.' Documents Result', $documents);
        return $documents;
    }

    public function getDocumentsContainingAll(array $tokens): array
    {
        $tmpSetKey = $this->findDocumentsContainingAll($tokens);
        $documents = $this->redis->zrange($tmpSetKey, 0, -1);
        $this->redis->del($tmpSetKey);
        $this->logger->debug(__METHOD__." Documents ALL $tmpSetKey", $documents);
        return $documents;
    }

    public function findDocumentsContainingAll(array $tokens): string
    {
        $keys = array_map([$this, 'getTokenDbKey'], $tokens);
        sort($keys); // create canonical representation of the query.
        $keys_list = implode('_', $keys);
        $dbId = 'search_all:' . $keys_list;
        $this->redis->zinterstore($dbId, $keys);
        $this->logger->debug(__METHOD__." Documents ALL $dbId");
        return $dbId;
    }

    public function getDocumentsContainingAny($tokens): array
    {
        $tmpSetKey = $this->findDocumentsContainingAny($tokens);
        $documents = $this->redis->zrange($tmpSetKey, 0, -1);
        $this->redis->del($tmpSetKey);
        $this->logger->debug(__METHOD__." Documents ANY $tmpSetKey", $documents);
        return $documents;
    }

    public function findDocumentsContainingAny($tokens): string
    {
        $keys = array_map([$this, 'getTokenDbKey'], $tokens);
        sort($keys); // create canonical representation of the query.
        $keys_list = implode('_', $keys);
        $dbId = 'search_any:' . $keys_list;
        $this->redis->zunionstore($dbId, $keys);

        $this->logger->debug(__METHOD__." Documents ANY $dbId");
        return $dbId;
    }

    private function removeFromInvertedIndex($documentId, ?array $tokens)
    {
        foreach ($tokens as $term) {
            $termId = self::TOKENS_KEY . $term;
            $this->redis->zrem($termId, $documentId);
        }
    }

    private function createInvertedIndex(string $documentId, array $tokens)
    {
        foreach ($tokens as $term) {
            $termId = self::TOKENS_KEY . $term;
            $termFrequency = $this->redis->zscore($termId, $documentId);
            $score = $termFrequency ? $termFrequency + 1 : 1;

            $this->redis->zadd($termId, [$documentId => $score]);
        }
    }

    private function getTokenDbKey(string $token): string
    {
        return self::TOKENS_KEY . $token;
    }

    private function getDocumentDbKey(string $documentId): string
    {
        return self::DOCUMENTS_KEY . $documentId;
    }


}
