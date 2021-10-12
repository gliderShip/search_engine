<?php

namespace App\Service;

use App\DTO\DocumentDto;
use Predis\Client;

class DocumentManager
{
    public const DOCUMENT_KEY = 'documents:';
    public const TOKEN_KEY = 'tokens:';

    /**
     * @var Client
     */
    public $redis;// TODO: make private

    public function __construct()
    {
        $this->redis = new Client();
    }

    public function upsert(DocumentDto $documentDto)
    {
        $documentId = $documentDto->getId();
        $tokens = $documentDto->getTokens();

        $documentId = self::DOCUMENT_KEY . $documentId;

        $previousContent = $this->redis->zrange($documentId, 0, -1);
        $this->removeFromInvertedIndex($documentId, $previousContent);
        $this->redis->del($documentId);
        $this->redis->zadd($documentId, array_flip($tokens));
        $this->createInvertedIndex($documentId, $tokens);

        return $this->redis->zrange($documentId, 0, -1);
    }

    private function removeFromInvertedIndex($documentId, ?array $tokens)
    {
        foreach ($tokens as $term) {
            $termId = self::TOKEN_KEY . $term;
            $this->redis->zrem($termId, $documentId);
        }
    }

    private function createInvertedIndex(string $documentId, array $tokens)
    {
        foreach ($tokens as $term) {
            $termId = self::TOKEN_KEY . $term;
            $termFrequency = $this->redis->zscore($termId, $documentId);
            $score = $termFrequency ? $termFrequency + 1 : 1;

            $this->redis->zadd($termId, [$documentId => $score]);
        }
    }

    public function findByToken($token): array
    {
        $key = self::TOKEN_KEY . $token;
        $documents = $this->redis->zrevrange($key, 0, -1, ['withscores' => TRUE]);
        return $documents;
    }

    public function getDocumentsContainingAll($tokens): array
    {
        $keys = array_map([$this, 'getTokenStorageKey'], $tokens);
        $keys_list = implode('_', $keys);
        $tmpSetKey = 'search_all:' . $keys_list;
        $this->redis->zinterstore($tmpSetKey, $keys);
        $documents = $this->redis->zrange($tmpSetKey, 0, -1);
        $this->redis->del($tmpSetKey);
        dump($documents);
        return $documents;
    }

    public function getDocumentsContainingAny($tokens): array
    {
        $keys = array_map([$this, 'getTokenStorageKey'], $tokens);
        $keys_list = implode('_', $keys);
        $tmpSetKey = 'search_any:' . $keys_list;
        $this->redis->zunionstore($tmpSetKey, $keys);
        $documents = $this->redis->zrange($tmpSetKey, 0, -1);
        $this->redis->del($tmpSetKey);
        return $documents;
    }

    private function getTokenStorageKey(string $token): string
    {
        return self::TOKEN_KEY . $token;
    }
}
