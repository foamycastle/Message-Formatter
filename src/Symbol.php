<?php

namespace FoamyCastle\Utils\MessageFormatter;

final class Symbol
{
    public const DEFAULT_INITIATOR="{";
    public const DEFAULT_TERMINATOR="}";
    public readonly string $name;
    public readonly SymbolData $data;

    public function __construct($name, $data)
    {
        $this->name = self::validateName($name) ? $name : "";
        $this->data = SymbolData::New($data);
    }
    public static function validateName($name): bool
    {
        if (!is_string($name)) {
            return false;
        }
        if (preg_match('/(?i)^[a-z][a-z0-9_]*$/', trim($name)) == 0) {
            return false;
        }
        return true;
    }
    public function __toString(): string
    {
        return (string)$this->data;
    }
    public static function find(string &$message,string $initiator=self::DEFAULT_INITIATOR,string $terminator=self::DEFAULT_TERMINATOR):SymbolTable|false
    {
        $_initiator=preg_quote($initiator);
        $_terminator=preg_quote($terminator);
        $regex=sprintf('/(?i)(?:%s(?<symbol>[a-z][a-z0-9_]*)(?:(?:=|::)(?:[a-z][a-z0-9_]*))?)%s/',$_initiator,$_terminator);
        $foundMatches=preg_match_all($regex,$message,$matches)>0;

        $symbolNamesArray=$matches['symbol'];
        return new SymbolTable($symbolNamesArray,$initiator,$terminator);

    }
}