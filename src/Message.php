<?php

namespace FoamyCastle\Utils\MessageFormatter;

/**
 * An object that holds an unformatted message
 */
class Message
{
    /**
     * Maximum length for any plain-text message
     */
    public const MAXLEN=8192;
    /**
     * @var string The unformatted message containing symbol and optional markup
     */
    protected string $rawMessage;
    /**
     * @var string|mixed the character(s) that signify the beginning of an optional inclusion
     */
    protected string $optionalInitiator;
    /**
     * @var string|mixed the character(s) that signify the end of an optional inclusion
     */
    protected string $optionalTerminator;
    /**
     * @var string|mixed the character(s) that signify the beginning of a symbol
     */
    protected string $symbolInitiator;
    /**
     * @var string|mixed the character(s) that signify the end of a symbol
     */
    protected string $symbolTerminator;
    /**
     * @var SymbolTable|false The array of symbols contained in a message
     */
    protected SymbolTable|false $symbolTable;

    /**
     * @param string $rawMessage The string containing plain text, symbol, and optional markup
     * @param string $optionalInitiator the characters that begin an optional inclusion
     * @param string $optionalTerminator the characters that end an optional inclusion
     * @param string $symbolInitiator the characters that begin a symbol
     * @param string $symbolTerminator the characters that end a symbol
     */
    public function __construct(
        string $rawMessage,
        $optionalInitiator=Optional::DEFAULT_INITIATOR,
        $optionalTerminator=Optional::DEFAULT_TERMINATOR,
        $symbolInitiator=Symbol::DEFAULT_INITIATOR,
        $symbolTerminator=Symbol::DEFAULT_TERMINATOR
    )
    {
        //auto-shave the message length down to acceptable max
        $this->rawMessage=substr($rawMessage,0,self::MAXLEN);
        $this->optionalInitiator=$optionalInitiator;
        $this->optionalTerminator=$optionalTerminator;
        $this->symbolInitiator=$symbolInitiator;
        $this->symbolTerminator=$symbolTerminator;
    }

    /**
     * Produces the final, fully-formatted message
     * @return string
     */
    public function __toString(): string
    {
        $message=$this->rawMessage;
        //find all optionals in message and create processor objects
        foreach (Optional::find($message) as $item) {
            $optional=new Optional($item,$this->optionalInitiator,$this->optionalTerminator,$this->symbolInitiator,$this->symbolTerminator);
            $message=str_replace($optional->original(),$optional,$message);
        }
        //After symbols have been processed, find remaining symbols
        $this->symbolTable=Symbol::find($this->rawMessage,$this->symbolInitiator,$this->symbolTerminator);
        //...and replace them
        $this->symbolTable->replace($message);
        return $message;
    }

    /**
     * Set the characters that will mark the beginning of an optional inclusion
     * @param string $optionalInitiator initiator characters
     * @return $this
     */
    public function setOptionalInitiator(string $optionalInitiator):static
    {
        $this->optionalInitiator=$optionalInitiator;
        return $this;
    }

    /**
     * Set the characters that will mark the end of an optional inclusion
     * @param string $optionalTerminator terminator characters
     * @return $this
     */
    public function setOptionalTerminator(string $optionalTerminator):static
    {
        $this->optionalTerminator=$optionalTerminator;
        return $this;
    }

    /**
     * Set the characters that will mark the beginning of a symbol
     * @param string $symbolInitiator initiator characters
     * @return $this
     */
    public function setSymbolInitiator(string $symbolInitiator):static
    {
        $this->symbolInitiator=$symbolInitiator;
        return $this;
    }

    /**
     * Set the characters that mark the end of a symbol
     * @param string $symbolTerminator terminator characters
     * @return $this
     */
    public function setSymbolTerminator(string $symbolTerminator): static
    {
        $this->symbolTerminator = $symbolTerminator;
        return $this;
    }
}