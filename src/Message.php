<?php

namespace FoamyCastle\Utils\MessageFormatter;


class Message
{
    public const MAXLEN=8192;
    protected string $rawMessage;

    public function __construct(string $rawMessage)
    {
        //auto-shave the message length down to acceptable max
        $this->rawMessage=substr($rawMessage,0,self::MAXLEN);
    }
    public function __toString(): string
    {
        $outputMessage=$this->rawMessage;
        Optional::Replace($outputMessage,"[","]");
        Symbol::Replace($outputMessage,"{","}");
        return $outputMessage;
    }

}