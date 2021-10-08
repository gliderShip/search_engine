<?php

namespace App\Validator;

use App\DTO\DocumentDto;
use App\Exception\ConsoleArgumentException;
use App\Exception\IndexException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

;

class IndexArgumentsValidator
{
    public const MISSING_DOCUMENT_ID_ERROR = "Missing mandatory doc-id!";
    public const MISSING_TOKENS_ERROR = "Please provide at least one token for this document!";
    public const DOCUMENT_ID_TYPE_ERROR = "Document id must be a positive integer";
    public const TOKEN_TYPE_ERROR = "tokens must be alfanumeric strings";
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @throws ConsoleArgumentException|IndexException
     */
    public function validate(DocumentDto $documentDto)
    {
        $errors = $this->validator->validate($documentDto);
        if(count($errors)){
            $error = $errors[0];
            throw new IndexException($error->getPropertyPath().' : '.$error->getMessage());
        }
    }
}
