<?php

namespace FoamyCastle\Utils\MessageFormatter;

use Generator;

/**
 * An object used to store and process an optional inclusion within a Message object
 */
final class Optional extends Message
{
    public const DEFAULT_INITIATOR="[";
    public const DEFAULT_TERMINATOR="]";
    /**
     * @var Generator $internalOptionals A function that locates and returns each sub-inclusion
     */
    private Generator $internalOptionals;

    /**
     * @param string $rawMessage The plain text of the optional inclusion
     * @param string $oInitiator The character(s) used to mark the beginning of a sub-inclusion
     * @param string $oTerminator The character(s) used to mark the end of a sub-inclusion
     * @param string $sInitiator The character(s) used to mark the beginning of a symbol
     * @param string $sTerminator The character(s) used to mark the end of a symbol
     */
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

    /**
     * Processes all sub-inclusions and symbols contain within the optional
     * @return string the fully-formatted and processed optional
     */
    public function __toString(): string
    {
        //trim input of initiator and terminator
        $outputMessage=$this->trimmed();
        //find all sub-inclusion and render them
        foreach ($this->internalOptionals as $internalOptional) {
            $optional=new self($internalOptional,$this->optionalInitiator,$this->optionalTerminator,$this->symbolInitiator,$this->symbolTerminator);
            $outputMessage=str_replace($optional->original(),$optional,$outputMessage);
        }

        //find all remaining symbols
        $this->symbolTable=Symbol::find($outputMessage,$this->symbolInitiator,$this->symbolTerminator);

        //if there are no symbols, simply return the processed message thus far
        if($this->symbolTable->symbolCount==0){
            return $outputMessage;
        }

        //replace the found symbols
        $replacedSymbols=$this->symbolTable->replace($outputMessage);

        /* if no symbols were replaced and there were symbols found in the message, return blank.
           The reasoning behind returning a blank string is what defines the optional inclusion.
           Think about a URL.  A URL may or may not contain a query string.  If the query string is blank,
           then the query signifier '?' should be left out of the URL altogether.

            http://www.google.com/search[?{query_string}]
        */
        if($replacedSymbols==0&&$this->symbolTable->symbolCount>0){
            return "";
        }
        return $outputMessage;
    }

    /**
     * Returns the unformatted optional text and includes the initiator and terminator strings
     * @return string
     */
    public function original():string
    {
        return $this->rawMessage;
    }

    /**
     * Returns the unformatted optional text but excludes the initiator and terminator strings
     * @return string
     */
    public function trimmed():string
    {
        $output=$this->rawMessage;
        if(str_starts_with($output,$this->optionalInitiator)&&str_ends_with($output,$this->optionalTerminator)){
            $output=substr($output,strlen($this->optionalInitiator),strlen($output)-strlen($this->optionalInitiator)-strlen($this->optionalTerminator));
        }
        return $output;
    }

    /**
     * Searches for and iterates over optional sub-inclusions within this inclusion
     * @param $initiator
     * @param $terminator
     * @return Generator
     */
    public function findWithin($initiator='',$terminator=''): Generator
    {
        if(empty($initiator)) $initiator=$this->optionalInitiator;
        if(empty($terminator)) $terminator=$this->optionalTerminator;

        yield from self::find($this->trimmed(),$initiator,$terminator);
    }

    /**
     * Find all optional inclusions within a given string
     * @param string $message
     * @param string $initiator
     * @param string $terminator
     * @return Generator An iterator that yields the identified optional inclusion string
     */
    public static function find(string $message,string $initiator=self::DEFAULT_INITIATOR,string $terminator=self::DEFAULT_TERMINATOR): Generator
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

            //Yields a string
            yield $newMessage;
            while(($term->current()<$init->current())&&($init->current()!==false)&&($term->current()!==false)){
                $term->next();
            }
        }while($init->current()!==false);
    }

    /**
     * An iterator function that locates each optional initiator string
     * @param string $message
     * @param string $initiator
     * @return Generator Yields an integer offset within the $message
     */
    private static function currentInitiator(string $message, string $initiator): Generator
    {
        $len=strlen($initiator);
        $messageLen=strlen($message);
        $offset=0;
        while(false!==($offset=strpos($message,$initiator,$offset)))
        {
            //Yields an integer
            yield $offset;
            $offset+=$len;
        }
        yield false;
    }

    /**
     * An iterator function that locates each terminator string
     * @param string $message
     * @param string $terminator
     * @return Generator Yields an integer offset within the message
     */
    private static function currentTerminator(string $message, string $terminator): Generator
    {
        $len=strlen($terminator);
        $messageLen=strlen($message);
        $offset=0;
        while(false!==($offset=strpos($message,$terminator,$offset)))
        {
            //Yields an integer
            yield $offset;
            $offset+=$len;
        }
        yield false;
    }
}