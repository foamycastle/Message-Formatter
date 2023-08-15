<?php

namespace FoamyCastle\Utils\MessageFormatter;
use FoamyCastle\Utils\MessageFormatter\SymbolData\DataObject\ObjectGet;

final class SymbolTable
{
    private static array $symbols;
    public int $symbolCount;

    private array $names;
    public string $initiator;
    public string $terminator;
    public function __construct(array $symbolsNames,string $initiator,string $terminator)
    {
        $this->names=$symbolsNames;
        $this->initiator=$initiator;
        $this->terminator=$terminator;
        $this->symbolCount=count($symbolsNames);
    }

    public function replace(string &$message):int
    {
        $_initiator=preg_quote($this->initiator);
        $_terminator=preg_quote($this->terminator);
        $count=$foundMatches=0;
        foreach ($this->names as $symbolName) {
            $nameRegexArray["/(?i)(?:$_initiator(?<symbolName>$symbolName)(?<argument>(?<operator>=|::)(?<param>[a-z][a-z0-9_]*)(?<parenthesis>\(\))?)?$_terminator)/"]=
            function(array $match) use (&$count){
                if(empty(SymbolTable::$symbols[$match['symbolName']])){
                    return "";
                }else{
                    $returnThis=SymbolTable::$symbols[$match['symbolName']];
                    if(isset($match['argument'])&&($returnThis->data instanceof ObjectGet)){
                        if(isset($match['operator'])&&isset($match['param'])){
                            $op=$match['operator'];
                            switch ($op){
                                case "::":
                                    $methodName=$match['param'];
                                    $returnThis=(string)$returnThis->data->getMethod($methodName);
                                case "=":
                                    $propertyName=$match['param'];
                                    $returnThis=(string)$returnThis->data->getProperty($propertyName);
                            }
                        }
                    }
                    if($returnThis!==""){
                        $count++;
                    }
                    return $returnThis;
                }
            };
        }
        if(empty($nameRegexArray)){
            return 0;
        }
        $message=preg_replace_callback_array($nameRegexArray,$message);
        return $count;
    }
    public static function import(array $symbols):void
    {
        foreach ($symbols as $symbol => $value) {
            if(Symbol::validateName($symbol)){
                self::$symbols[$symbol]=new Symbol($symbol,$value);
            }
        }
    }

    public static function hasSymbol(string $name):bool
    {
        return isset(self::$symbols[$name])&&(self::$symbols[$name] instanceof Symbol);
    }


}