<?php

namespace App\Model;

use App\DTO\DocumentDto;

interface StorageInterface
{

    public function findAll():array;

    /**
     * @param DocumentDto $documentDto
     * @return string The storage document Id.
     */
    public function upsert(DocumentDto $documentDto): string;

    public function getEntityById(string $dbId): array;

    /**
     * @param string $dbId
     * @return array
     * Return the document content
     */
    public function getDocumentById(string $dbId): array;

    /**
     * @param $token
     * @return string.
     * Return the database id of the document collection containing the token in their content
     */
    public function findByToken($token): string;

    /**
     * @param $token
     * @return array.
     * Return an array of documents containing the token in their content
     */
    public function getByToken($token): array;

    public function getDocumentsContainingAny(array $tokens): array;

    public function getDocumentsContainingAll(array $tokens): array;

}
