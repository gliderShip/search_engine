<?php

namespace App\Model;

interface TokenInterface
{
    public static function getType(): string;
    public function getLexeme(): string;
    public function getStartPosition(): int;
    public function getEndPosition(): int;
    public static function isValidLexeme(string $lexeme): bool;
}
