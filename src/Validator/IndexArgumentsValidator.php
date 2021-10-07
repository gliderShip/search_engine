<?php

namespace App\Validator;

use App\Exception\ConsoleArgumentException;
use App\Exception\IndexException;

;

class IndexArgumentsValidator
{
    public const MISSING_DOCUMENT_ID_ERROR = "Missing mandatory doc-id!";
    public const MISSING_TOKENS_ERROR = "Please provide at least one token for this document!";
    public const DOCUMENT_ID_TYPE_ERROR = "Document id must be a positive integer";
    public const TOKEN_TYPE_ERROR = "tokens must be a positive integer";

    /**
     * @throws ConsoleArgumentException|IndexException
     */
    public function validate($documetId, $tokens)
    {
        if (empty($documetId)) {
            throw new ConsoleArgumentException(self::MISSING_DOCUMENT_ID_ERROR);
        }

        if (empty($tokens)) {
            throw new ConsoleArgumentException(self::MISSING_TOKENS_ERROR);
        }

        if (!(ctype_digit($documetId))) {
            throw new IndexException(self::DOCUMENT_ID_TYPE_ERROR);
        }

        foreach ($tokens as $token)
            if (!(ctype_alnum($token))) {
                throw new IndexException(self::DOCUMENT_ID_TYPE_ERROR . " => $token");
            }
    }
}
