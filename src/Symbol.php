<?php

namespace FoamyCastle\Utils\MessageFormatter;
use FoamyCastle\Utils\DataResolver;
use Generator;

class Symbol
{
    public const DEFAULT_INITIATOR="{";
    public const DEFAULT_TERMINATOR="}";
    private static array $symbols;
    public static string $initiator;
    public static string $terminator;
    public static function import(array $symbols):void
    {
        do{
            $key=key($symbols);
            $value=current($symbols);
            if(is_int($key)|is_string($key)){
                self::$symbols[$key]=(string)(new DataResolver($value));
            }
        }while(false!==next($symbols));
    }

    public static function set(string $key,$value):void
    {
        self::$symbols[$key]=new DataResolver($value);
    }

    public static function remove(string $key):void
    {
        if(isset(self::$symbols[$key])){
            unset(self::$symbols[$key]);
        }
    }
    public static function getSymbolData(string $key):?string
    {
        if(isset(self::$symbols[$key])){
            return self::$symbols[$key];
        }
        return null;
    }

    public static function getSymbolList():?array{
        if(!empty(self::$symbols)){
            return array_keys(self::$symbols);
        }
        return null;
    }

    public static function Find(string $message, string $initiator=self::DEFAULT_INITIATOR, string $terminator=self::DEFAULT_TERMINATOR):Generator
    {
        $matched=[];
         $searchForSymbols=self::getSymbolList();
        $areSymbols=preg_match_all("/".$initiator."(".join("|",$searchForSymbols).")".$terminator."/",$message,$matched);
        $matched=$matched[1]??[];
        if($areSymbols>0||$areSymbols!==false){
            do{
                if(isset(self::$symbols[current($matched)])) {
                    yield current($matched);
                }
            }while(next($matched));
        }
    }

    public static function Replace(string &$message, string $initiator=self::DEFAULT_INITIATOR, string $terminator=self::DEFAULT_TERMINATOR):bool
    {
        $symbolGenerator=self::Find($message,$initiator,$terminator);
        $count=$replaceCount=0;$didReplace=false;
        foreach ($symbolGenerator as $symbol) {
            $symbolData=(string)Symbol::getSymbolData($symbol);
            if(null===$symbolData) {
                $message=str_replace($initiator.$symbol.$terminator,"",$message,$count);
                continue;
            }
            $message=str_replace($initiator.$symbol.$terminator,$symbolData,$message,$count);
            if($symbolData!=""&&!$didReplace){
                $didReplace=true;
            }
        }
        return $didReplace;
    }

    public static function setInitiator(string $initiator):void
    {
        self::$initiator=$initiator;
    }

    public static function setTerminator(string $terminator):void
    {
        self::$terminator=$terminator;
    }
    public static function getInitiator():string
    {
        return self::$initiator ?? self::DEFAULT_INITIATOR;
    }
    public static function getTerminator():string
    {
        return self::$terminator ?? self::DEFAULT_TERMINATOR;
    }

}