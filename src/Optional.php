<?php

namespace FoamyCastle\Utils\MessageFormatter;

use FoamyCastle\Utils\MessageFormatter\Message;
use PHPUnit\Event\Code\Test;

final class Optional extends Message
{
    public const DEFAULT_INITIATOR="[";
    public const DEFAULT_TERMINATOR="]";
    private \Generator $internalOptionals;
    public function __construct(
        string $rawMessage,
        string $oInitiator=self::DEFAULT_INITIATOR,
        string $oTerminator=self::DEFAULT_TERMINATOR,
        string $sInitiator=Symbol::DEFAULT_INITIATOR,
        string $sTerminator=Symbol::DEFAULT_TERMINATOR
    )
    {
        parent::__construct($rawMessage);

        $this->internalOptionals=$this->findWithin();
        $this->symbolInitiator=$sInitiator;
        $this->symbolTerminator=$sTerminator;
        $this->optionalInitiator=$oInitiator;
        $this->optionalTerminator=$oTerminator;
    }
    public function __toString(): string
    {
        $outputMessage=$this->trimmed();
        foreach ($this->internalOptionals as $internalOptional) {
            $optional=new self($internalOptional,$this->optionalInitiator,$this->optionalTerminator,$this->symbolInitiator,$this->symbolTerminator);
            $outputMessage=str_replace($optional->original(),$optional,$outputMessage);
        }
        $this->symbolTable=Symbol::find($outputMessage,$this->symbolInitiator,$this->symbolTerminator);
        if($this->symbolTable->symbolCount==0){
            return $outputMessage;
        }
        $replacedSymbols=$this->symbolTable->replace($outputMessage);
        if($replacedSymbols==0&&$this->symbolTable->symbolCount>0){
            return "";
        }
        return $outputMessage;
    }

    public function original():string
    {
        return $this->rawMessage;
    }
    public function trimmed():string
    {
        $output=$this->rawMessage;
        if(str_starts_with($output,$this->optionalInitiator)&&str_ends_with($output,$this->optionalTerminator)){
            $output=substr($output,strlen($this->optionalInitiator),strlen($output)-strlen($this->optionalInitiator)-strlen($this->optionalTerminator));
        }
        return $output;
    }
    public function findWithin($initiator='',$terminator=''):\Generator
    {
        if(empty($initiator)) $initiator=$this->optionalInitiator;
        if(empty($terminator)) $terminator=$this->optionalTerminator;

        yield from self::find($this->trimmed(),$initiator,$terminator);
    }

    public static function find(string $message,string $initiator=self::DEFAULT_INITIATOR,string $terminator=self::DEFAULT_TERMINATOR):\Generator
    {
        $init=self::currentInitiator($message,$initiator);
        $term=self::currentTerminator($message,$terminator);
        $messageLen=strlen($message);
        $termLen=strlen($terminator);
        $beginningOffset=$endingOffset=$count=0;
        do{
            if($init->current()===false) break;
            if($term->current()===false) break;
            if($term->current()>$init->current()){
                $beginningOffset=$init->current();
            }
            while($term->current()>$init->current()){
                $count++;
                $init->next();
                if($init->current()===false){
                    break;
                }
            }
            while($count>0){
                $count--;
                $endingOffset=$term->current()+strlen($terminator);
                $term->next();
                if($term->current()===false&&$count>0){
                    if($endingOffset>$messageLen-1){
                        $endingOffset=$messageLen-1;
                    }
                    break;
                }
            }
            $newMessage=substr($message,$beginningOffset,$endingOffset-$beginningOffset);

            yield $newMessage;
            while(($term->current()<$init->current())&&($init->current()!==false)&&($term->current()!==false)){
                $term->next();
            }
        }while($init->current()!==false);
    }
    private static function currentInitiator(string $message, string $initiator):\Generator
    {
        $len=strlen($initiator);
        $messageLen=strlen($message);
        $offset=0;
        while(false!==($offset=strpos($message,$initiator,$offset)))
        {
            yield $offset;
            $offset+=$len;
        }
        yield false;
    }

    private static function currentTerminator(string $message, string $terminator):\Generator
    {
        $len=strlen($terminator);
        $messageLen=strlen($message);
        $offset=0;
        while(false!==($offset=strpos($message,$terminator,$offset)))
        {
            yield $offset;
            $offset+=$len;
        }
        yield false;
    }
}