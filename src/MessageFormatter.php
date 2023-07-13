<?php

namespace FoamyCastle\Utils\MessageFormatter;

use FoamyCastle\Utils\DataResolver;

class MessageFormatter
{
    private const REPLACEMENT_NONE=0;
    private const REPLACEMENT_YES=1;
    private const REPLACEMENT_NO_SYMBOLS=2;
    private const REGEX_FIND_OPTIONALS="/(?<optional>\[(?:(?<optional_contents>[^\[\]]+)|(?R))*\])/";
    private const REGEX_FIND_SYMBOL="/(?:{(?<symbol>[^\r\n}]+)})/";
    /**
     * when resolving symbols, callables may be used. when called, a callable may resolve to
     * another callable reference. this loop could theoretically continue indefinitely. this const
     * acts as an upper limit.  if a callable reference does not resolve to a scalar value or simple array,
     * stop trying after this many tries.
     */
    private const CALLABLE_COUNTER_MAX=1000;
    /**
     * @var string The raw message string containing readable text and unresolved symbols
     */
    private $rawMessage;
    /**
     * An array containing plain-text symbols as keys and raw data or unresolved references as values
     * @var array
     * @psalm-var array<string,string|int|float|bool|object|array|Closure>
     */
    private $symbolTable;

    /**
     * contains the symbols that have been resolved from the symbol table
     * @var $resolvedSymbols array<string,string|int|float|bool|object|array|Closure>
     */
    private $resolvedSymbols;

    public function __construct(string $rawMessage="",array $symbolTable=[])
    {
        $this->rawMessage=$rawMessage;
        $this->symbolTable=$symbolTable;
    }
    public function __invoke(string $message="",$symbolTable=[]):self
    {
        $this->rawMessage=$message;
        $this->symbolTable=$symbolTable;
        return $this;
    }
    public function __toString():string{
        return $this->getMessage();
    }

    /**
     * Add a symbol to the symbol table
     * @param array|string $symbol plain-text symbol identifier. Do not include any curly braces or other bounding characters
     * @param array|string|object|int|float|bool|\Closure $value the object that will replace the symbol
     * @return $this
     */
    public function addSymbol(array|string $symbol, $value=null):self{
        if(is_array($symbol)){
            reset($symbol);
            //$symbol = ['symbol', 'value']
            if(is_string($symbol[0])) {
                $this->symbolTable[$symbol[0]] = $symbol[1];
            }
            //$symbol = ['symbol'=>'value']
            if(is_string(key($symbol))){
                $key=key($symbol);
                $this->symbolTable[$key]=$symbol[$key];
            }
            //$symbol = 'symbol' $value='value'
        }elseif(is_string($symbol)&&null!==$value) {
            $this->symbolTable[$symbol] = $value;
        }
        return $this;
    }

    public function setSymbolTable(array $symbolTable):self{
        if(empty($symbolTable)) return $this;
        $this->symbolTable=$symbolTable;
        return $this;
    }

    /**
     * Remove a symbol from the symbol table
     * @param string $symbol the plain-text symbol identifier
     * @return $this
     */
    public function removeSymbol(string $symbol):self{
        if(isset($this->symbolTable[$symbol])){
            unset($this->symbolTable[$symbol]);
        }
        return $this;
    }

    public function clearSymbolTable():self{
        unset($this->symbolTable);
        return $this;
    }

    /**
     * Output the fully formatted and replaced message
     * @return string
     */
    public function getMessage():string{
        return $this->performReplacement();
    }

    /**
     * Set a string containing symbols to be replaced with values
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message):self{
        $this->rawMessage=$message;
        return $this;
    }

    /**
     * Iterate through the symbol table and resolve each value that will replace each symbol
     * @return array<string,mixed> the array of ['symbol'=>'value']
     */
    private function resolveSymbol(string $symbolName):string
    {
        if(!$this->symbolIsResolved($symbolName)) {
            $this->resolvedSymbols[$symbolName]=(new DataResolver($this->symbolTable[$symbolName]));
        }
        return $this->resolvedSymbols[$symbolName];
    }

    /**
     * Indicates whether a symbol has been resolved
     * @return array list of symbol identifiers
     */
    private function symbolIsResolved(string $symbolName):bool{
        return !empty($this->resolvedSymbols[$symbolName]);
    }

    /**
     * Iterate through the symbol table and perform a find-and-replace on the rawMessage property
     * @return string a fully-processed string, symbols having been replaced with resolved values
     */
    private function performReplacement():string{
        $outputMessage=$this->rawMessage;
        $this->replaceOptionals($outputMessage);
        $this->replaceSymbols($outputMessage);
        return $outputMessage;
    }
    private function findOptionals(string $input):array|false
    {
        return preg_match_all(self::REGEX_FIND_OPTIONALS,$input,$optionals)>0
            ? $optionals['optional']
            : false;
    }
    private function getOptionalText(string $optional):string
    {
        return trim($optional,"[]");
    }
    private function getSymbolText(string $symbol):string
    {
        return trim($symbol,'{}');
    }
    private function findSymbols(string $optional):array|false
    {
        return preg_match_all(self::REGEX_FIND_SYMBOL,$optional,$foundSymbols)>0
            ? $foundSymbols['symbol']
            : false;
    }
    private function replaceOptionals(string &$input):void
    {
        $optionals=$this->findOptionals($input);
        if(false!==$optionals){
            foreach ($optionals as $matchOptional) {
                $contents=$this->getOptionalText($matchOptional);
                $this->replaceOptionals($contents);
                $replacementStatus=$this->replaceSymbols($contents);
                if($replacementStatus==self::REPLACEMENT_YES) {
                    $input = str_replace($matchOptional, $contents, $input);
                }elseif($replacementStatus==self::REPLACEMENT_NONE){
                    $input = str_replace($matchOptional, "", $input);
                }
            }
        }
    }
    private function replaceSymbols(string &$input):int
    {
        $performedReplace=self::REPLACEMENT_NONE;
        $symbols=$this->findSymbols($input);
        if(false!==$symbols){
            foreach ($symbols as $symbol) {
                $symbolName=$this->getSymbolText($symbol);
                $symbolData=$this->resolveSymbol($symbolName);
                if(!empty($symbolData)){
                    $input=str_replace("{".$symbol."}",$symbolData,$input);
                    $performedReplace=self::REPLACEMENT_YES;
                }
            }
        }else{
            $performedReplace=self::REPLACEMENT_NO_SYMBOLS;
        }
        return $performedReplace;
    }

}
