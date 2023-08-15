<?php

namespace FoamyCastle\Utils\MessageFormatter;

final class Symbol
{
    public const DEFAULT_INITIATOR="{";
    public const DEFAULT_TERMINATOR="}";
    /**
     * The symbol identifier
     * @var string|mixed
     */
    public readonly string $name;
    /**
     * An object that contain the symbols data and renders it to a string
     * @var SymbolData
     */
    public readonly SymbolData $data;

    public function __construct($name, $data)
    {
        $this->name = self::validateName($name) ? $name : "";
        $this->data = SymbolData::New($data);
    }

    /**
     * Validate the characters contained in the name
     * @param string $name The name must begin with [a-z]
     * @return bool TRUE if validation passes, FALSE if not
     */
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

    /**
     * Forces the child classes to render the data to a string representation
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->data;
    }

    /**
     * Locates all symbols within a string.
     * @param string $message
     * @param string $initiator
     * @param string $terminator
     * @return SymbolTable|false
     */
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