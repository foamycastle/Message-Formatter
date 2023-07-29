<?php

namespace FoamyCastle\Utils\MessageFormatter;

use Generator;

class Optional
{
    public const DEFAULT_INITIATOR = '[';
    public const DEFAULT_TERMINATOR = ']';
    public readonly string $original;
    private readonly string $regex;
    public readonly string $terminator;
    public readonly string $initiator;
    private bool $containsOptionals;
    private Generator $internalOptionals;


    private function __construct(string $rawMessage, string $initiator = self::DEFAULT_INITIATOR, string $terminator = self::DEFAULT_TERMINATOR)
    {
        //
        $this->original = $rawMessage;
        $this->initiator = $initiator;
        $this->terminator = $terminator;
        $this->internalOptionals = self::Find($this->extractContent(), $this->initiator, $this->terminator);
    }
    private function extractContent():string
    {
        $initLen = strlen($this->initiator);
        $termLen = strlen($this->terminator);
        $origLen = strlen($this->original);
        $initFlag = $this->original[0] == $this->initiator;
        $termFlag = $this->original[$origLen - 1] == $this->terminator;
        if ($initFlag && $termFlag) {
            return substr($this->original, $initLen, -($termLen));
        }else{
            return $this->original;
        }
    }
    public function __toString(): string
    {
        $outputString=$this->original;
        foreach ($this->internalOptionals as $optional) {
            $outputString=str_replace($optional->original,$optional,$outputString);
        }
        $didReplace = Symbol::Replace($outputString, Symbol::getInitiator(), Symbol::getTerminator());

        return $didReplace ? $outputString : "";
    }

    private static function extractMessage($message,$init,$term):string
    {
        $initLen = strlen($init);
        $termLen = strlen($term);
        $origLen = strlen($message);
        $initFlag = $message[0] == $init;
        $termFlag = $message[$origLen - 1] == $term;
        if ($initFlag && $termFlag) {
            return substr($message, $initLen, -($termLen));
        }else{
            return $message;
        }
    }

    public static function Find(string $rawMessage, string $initiator = self::DEFAULT_INITIATOR, string $terminator = self::DEFAULT_TERMINATOR): Generator
    {
        $_initiator=preg_quote($initiator);
        $_terminator=preg_quote($terminator);
        $regex="/$_initiator(?:[^$_initiator$_terminator]+|(?R))*$_terminator/";
        $matches=[];
        $hasMatches=preg_match_all($regex,$rawMessage,$matches);
        while(current($matches[0])!==false){
            yield new Optional(current($matches[0]),$initiator,$terminator);
            next($matches[0]);
        }
    }

    public static function Replace(string &$rawMessage, string $initiator = self::DEFAULT_INITIATOR, string $terminator = self::DEFAULT_TERMINATOR): int
    {
        $optionalContent=self::extractMessage($rawMessage,$initiator,$terminator);
        $optionals = self::Find($optionalContent, $initiator, $terminator);
        $count = $replaceCount = 0;
        foreach ($optionals as $optional) {
            $rawMessage = str_replace($optional->original, $optional, $rawMessage, $count);
            $replaceCount += $count;
        }
        return $replaceCount;
    }
}