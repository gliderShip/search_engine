<?php

namespace App\Service;

use App\DTO\CollectionDto;
use App\DTO\DocumentDto;
use App\Entity\Collection;
use App\Entity\Document;

interface DocumentManager
{
    /**
     * @param DocumentDto $documentDto
     * @return string The storage document Id.
     */
    public function upsert(DocumentDto $documentDto): Document;

    /**
     * @param $keyword
     * Return a collection containing the document Ids of the matched documents
     */
    public function getDocumentsByKeyword($keyword): Collection;

    public function getDocumentsContainingAny(array $keywords): Collection;

    public function getCommonDocuments(array $collectionArray): Collection;

    public function getAllDocuments(array $collectionArray): Collection;

    public function getDocumentsContainingAll(array $keywords): Collection;

    public function computeUnion(array $setsKeys): string;

    public function computeIntersection(array $setsKeys): string;

    public function getCollectionDto(Collection $collection): CollectionDto;


}
