<?php

namespace FoamyCastle\Utils\MessageFormatter;


class Message
{
    public const MAXLEN=8192;
    protected string $rawMessage;
    protected string $optionalInitiator;
    protected string $optionalTerminator;
    protected string $symbolInitiator;
    protected string $symbolTerminator;
    protected SymbolTable|false $symbolTable;

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
    public function __toString(): string
    {
        $message=$this->rawMessage;
        foreach (Optional::find($message) as $item) {
            $optional=new Optional($item,$this->optionalInitiator,$this->optionalTerminator,$this->symbolInitiator,$this->symbolTerminator);
            $message=str_replace($optional->original(),$optional,$message);
        }
        $this->symbolTable=Symbol::find($this->rawMessage,$this->symbolInitiator,$this->symbolTerminator);
        $this->symbolTable->replace($message);
        return $message;
    }
    public function setOptionalInitiator(string $optionalInitiator):static
    {
        $this->optionalInitiator=$optionalInitiator;
        return $this;
    }

    public function setOptionalTerminator(string $optionalTerminator):static
    {
        $this->optionalTerminator=$optionalTerminator;
        return $this;
    }

    public function setSymbolInitiator(string $symbolInitiator):static
    {
        $this->symbolInitiator=$symbolInitiator;
        return $this;
    }

    public function setSymbolTerminator(string $symbolTerminator): static
    {
        $this->symbolTerminator = $symbolTerminator;
        return $this;
    }
}