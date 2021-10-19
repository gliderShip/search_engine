<?php

namespace App\Service;

use App\DTO\CollectionDto;
use App\DTO\DocumentDto;
use App\Entity\Collection;
use App\Entity\Document;
use Predis\Client;
use Psr\Log\LoggerInterface;

class RedisManager implements DocumentManager
{
    public const DOCUMENTS_KEY = 'documents:';
    public const TOKENS_KEY = 'tokens:';
    public const UNION_KEY = 'union:';
    public const INTERSECTION_KEY = 'intersection:';
    public const CACHE_TTL = 5;

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

    public function upsert(DocumentDto $documentDto): Document
    {
        $documentId = $documentDto->getId();
        $tokens = $documentDto->getTokens();

        $dbId = $this->getDocumentDbKey($documentId);

        $previousContent = $this->redis->zrange($dbId, 0, -1, ['withscores' => TRUE]);
        $this->removeFromInvertedIndex($dbId, $previousContent);
        $this->redis->del($dbId);
        $this->redis->zadd($dbId, array_flip($tokens));
        $this->createInvertedIndex($dbId, $tokens);

        $document = new Document($dbId, $documentId, $tokens);
        return $document;
    }

    public function getSortedSetById(string $dbId): array
    {
        return $this->redis->zrange($dbId, 0, -1, ['withscores' => TRUE]);
    }

    public function getDocumentById(string $dbId): Document
    {
        $content = $this->redis->zrange($dbId, 0, -1, ['withscores' => TRUE]);
        $documentId = $this->getDocumentId($dbId);

        $document = new Document($dbId, $documentId, $content);
        return $document;
    }

    public function getDocumentsByKeyword($keyword): Collection
    {
        $keywordDbKey = $this->getKeywordDbKey($keyword);
        $documents = $this->redis->zrevrange($keywordDbKey, 0, -1, ['withscores' => TRUE]);
        $this->logger->debug(__METHOD__ . " Result ->", ['collectionId' => $keywordDbKey, 'keyword' => $keyword, 'documents' => $documents]);

        return new Collection($keywordDbKey, $documents);
    }

    public function getDocumentsContainingAll(array $keywords): Collection
    {
        $collectionId = $this->findDocumentsContainingAll($keywords);
        $documents = $this->redis->zrange($collectionId, 0, -1, ['withscores' => TRUE]);
        $this->logger->debug(__METHOD__ . " Result ->", ['collectionId' => $collectionId, 'keywords' => $keywords, 'documents' => $documents]);
        return new Collection($collectionId, $documents);
    }

    public function getCommonDocuments(array $collectionArray): Collection
    {
        $setsKeys = [];
        foreach ($collectionArray as $collection){
            $setsKeys[] = $collection->getId();
        }

        $commonDocumentsSetId =  $this->computeIntersection($setsKeys);
        $documents = $this->redis->zrange($commonDocumentsSetId, 0, -1, ['withscores' => TRUE]);
        return new Collection($commonDocumentsSetId, $documents);
    }

    public function getAllDocuments(array $collectionArray): Collection
    {
        $setsKeys = [];
        foreach ($collectionArray as $collection){
            $setsKeys[] = $collection->getId();
        }

        $allDocumentsSetId =  $this->computeUnion($setsKeys);
        $documents = $this->redis->zrange($allDocumentsSetId, 0, -1, ['withscores' => TRUE]);
        return new Collection($allDocumentsSetId, $documents);
    }

    public function getDocumentsContainingAny($keywords): Collection
    {
        $collectionId = $this->findDocumentsContainingAny($keywords);
        $documents = $this->redis->zrange($collectionId, 0, -1, ['withscores' => TRUE]);
        $this->logger->debug(__METHOD__ . " Result ->", ['collectionId' => $collectionId, 'keywords' => $keywords, 'documents' => $documents]);
        return new Collection($collectionId, $documents);
    }

    public function getCollectionDto(Collection $collection): CollectionDto
    {
        $id = $collection->getId();
        $payload = $collection->getContent();
        $content = [];
        foreach ($payload as $key => $value){
            $content[$this->getDocumentId($key)] = $value;
        }

        return new CollectionDto($id, $content);
    }

    private function removeFromInvertedIndex($documentId, ?array $tokens)
    {
        foreach ($tokens as $term) {
            $termDbKey = $this->getKeywordDbKey($term);
            $this->redis->zrem($termDbKey, $documentId);
        }
    }

    private function createInvertedIndex(string $documentId, array $tokens)
    {
        foreach ($tokens as $term) {
            $termDbKey = $this->getKeywordDbKey($term);
            $termFrequency = $this->redis->zscore($termDbKey, $documentId);
            $score = $termFrequency ? $termFrequency + 1 : 1;

            $this->redis->zadd($termDbKey, [$documentId => $score]);
        }
    }

    /**
     * @param array $keywords
     * @return Return the collection Id which hold the requested document Ids
     */
    private function findDocumentsContainingAll(array $keywords): string
    {
        $keywordIds = array_map([$this, 'getKeywordDbKey'], $keywords);
        return $this->computeUnion($keywordIds);
    }

    /**
     * @param $keywords
     * @return Return the collection Id which hold the requested document Ids
     */
    private function findDocumentsContainingAny($keywords): string
    {
        $keywordIds = array_map([$this, 'getKeywordDbKey'], $keywords);
        return $this->computeUnion($keywordIds);
    }

    public function computeUnion(array $setsKeys): string
    {
        $dbResultId = $this->generateCollectionCanonicalKey(self::UNION_KEY, $setsKeys);

        foreach ($setsKeys as $key) {
            if (!$this->redis->exists($key)) {
                throw new \BadMethodCallException("Set :-> $key not found!");
            }
        }

        if(!$this->redis->exists($dbResultId)){
            $this->redis->zunionstore($dbResultId, $setsKeys);
            $this->logger->debug(__METHOD__ . " $dbResultId ->: CACHE MISS");
        } else{
            $this->logger->debug(__METHOD__ . " $dbResultId ->: CACHE HIT");
        }

        if(self::CACHE_TTL){
            $this->redis->expire($dbResultId, self::CACHE_TTL);
        }

        $this->logger->debug(__METHOD__ . " Result :=> $dbResultId");

        return $dbResultId;
    }

    public function computeIntersection(array $setsKeys): string
    {
        $dbResultId = $this->generateCollectionCanonicalKey(self::INTERSECTION_KEY, $setsKeys);

        foreach ($setsKeys as $key) {
            if (!$this->redis->exists($key)) {
                throw new \BadMethodCallException("Set :-> $key not found!");
            }
        }

        if(!$this->redis->exists($dbResultId)){
            $this->redis->zinterstore($dbResultId, $setsKeys);
            $this->logger->debug(__METHOD__ . " $dbResultId ->: CACHE MISS");
        } else{
            $this->logger->debug(__METHOD__ . " $dbResultId ->: CACHE HIT");
        }

        if(self::CACHE_TTL){
            $this->redis->expire($dbResultId, self::CACHE_TTL);
        }

        $this->logger->debug(__METHOD__ . " Result :=> $dbResultId");

        return $dbResultId;
    }

    private function getDocumentId(string $documentDbKey){
        if (substr($documentDbKey, 0, strlen(self::DOCUMENTS_KEY)) != self::DOCUMENTS_KEY) {
            throw new \BadMethodCallException("Invalid database id ->:$documentDbKey for redis storage");
        }

        return substr($documentDbKey, strlen(self::DOCUMENTS_KEY));
    }

    private function getKeywordId(string $keywordDbKey): string
    {
        if (substr($keywordDbKey, 0, strlen(self::TOKENS_KEY)) != self::TOKENS_KEY) {
            throw new \BadMethodCallException("Invalid database id ->:$keywordDbKey for redis storage");
        }

        return substr($keywordDbKey, strlen(self::TOKENS_KEY));
    }

    private function getKeywordDbKey(string $keywordId): string
    {
        return self::TOKENS_KEY . $keywordId;
    }
    private function getDocumentDbKey(string $documentId): string
    {
        return self::DOCUMENTS_KEY . $documentId;
    }

    private function generateCollectionCanonicalKey(string $prefix, array $collectionItems) :string{

        sort($collectionItems);
        $item_list = implode('_', $collectionItems);
        return $prefix.$item_list;

    }


}
