<?php

namespace App\Service;

use App\DTO\DocumentDto;
use App\Model\StorageInterface;
use App\Model\TokenOperator;

class DocumentManager
{
    private StorageInterface $storageManager;

    public function __construct(StorageInterface $storage)
    {
        $this->storageManager = $storage;
    }

    /**
     * @return StorageInterface
     */
    public function getStorageManager(): StorageInterface
    {
        return $this->storageManager;
    }

    public function upsert(DocumentDto $documentDto): array
    {
        $dbId = $this->storageManager->upsert($documentDto);
        return $this->storageManager->getDocumentById($dbId);
    }

    public function getByDbId($dbId): array
    {
        return $this->storageManager->getDocumentById($dbId);
    }

    public function findByToken($token): string
    {
        return $this->storageManager->findByToken($token);
    }

    public function getByToken($token): array
    {
        return $this->storageManager->getByToken($token);
    }

    public function executeBinaryOperation(string $leftLiteral, TokenOperator $operator, string $rightLiteral): string
    {
        $operation = $operator->getLexeme();
        switch ($operation) {
            case TokenOperator::AND_OPERATOR['LEXEME']:
                $result = $this->storageManager->findDocumentsContainingAll([$leftLiteral, $rightLiteral]);
                break;
            case TokenOperator::OR_OPERATOR['LEXEME']:
                $result = $this->storageManager->findDocumentsContainingAny([$leftLiteral, $rightLiteral]);
                break;
            default:
                throw new \BadMethodCallException("Unsupported operation ->:" . $operation);
        }

        return $result;
    }


}
