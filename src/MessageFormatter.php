<?php

namespace FoamyCastle\Utils\MessageFormatter;

class MessageFormatter
{
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
     * An array that contains symbol templates. A symbol template consists of the characters that surround a '%s' identifier. 
     *
     * Examples: [%s] {{%s}} %%s% where '%s' will be replaced with the plain-text symbol identifier
     * 
     * The default symbol template is created at instantiation but other symbol templates can be added after the fact.
     * 
     * @var string[]
     * @psalm-var string[]
     */
    private $symbolTemplates=[];

    /**
     * The default symbol template is '{%s}' where '%s' is the alphanumeric symbol name. The symbol template can be changed
     * and other symbol templates can be added
     * @param string $rawMessage
     * @param array $symbolTable
     */

    public function __construct(string $rawMessage="",array $symbolTable=[])
    {
        $this->rawMessage=$rawMessage;
        $this->symbolTable=$symbolTable;
        $this->symbolTemplates[]='{%s}';
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
     * Add a symbol template to the search and replace algorithm
     * @param string $symbolBegin
     * @param string $symbolEnd
     * @return $this
     */
    public function addTemplate(string $symbolBegin,string $symbolEnd):self{
        if($symbolBegin==""||$symbolEnd=="") return $this;
        $this->symbolTemplates[]="$symbolBegin%s$symbolEnd";
        return $this;
    }

    /**
     * Remove a symbol template from the search and replace algorithm
     * @param string $symbolBegin
     * @param string $symbolEnd
     * @return $this
     */
    public function removeTemplate(string $symbolBegin, string $symbolEnd):self{
        if($symbolBegin==""||$symbolEnd=="") return $this;
        $searchFor="$symbolBegin%s$symbolEnd";
        $isPresent=array_search($searchFor,$this->symbolTemplates);
        if(false===$isPresent){
            //symbol template was not found
            return $this;
        }
        //symbol template was found, unset it
        unset($this->symbolTemplates[$isPresent]);
        return $this;
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
     * Returns an array of possible templates that a symbol could be found in
     * @param string $symbol plain-text symbol
     * @return array
     */
    private function getPossibleSymbols(string $symbol):array{

        $templates= $this->symbolTemplates;
        $outputArray=[];
        foreach ($templates as $template) {
            $outputArray[]=str_replace('%s',$symbol,$template);
        }
        return $outputArray;
    }

    /**
     * Iterate through the symbol table and resolve each value that will replace each symbol
     * @return array<string,mixed> the array of ['symbol'=>'value']
     */
    private function resolveSymbols():array
    {
        $outputArray=[];
        foreach ($this->symbolTable as $symbol=>$object) {
            $outputArray[$symbol]=$this->resolveObjectToString($object);
        }
        return $outputArray;
    }

    /**
     * Determine if an object can be cast to a string
     * @param object $data
     * @return bool TRUE if object can be cast to a string
     */
    private function objectIsStringable(object $data):bool{
        $implement=class_implements($data,false);
        $hasStringMethod=method_exists($data,'__toString');
        return in_array(\Stringable::class,$implement)||$hasStringMethod;
    }

    /**
     * Similar to print_r($array,true). The difference with this function is cleaner output formatting
     * @param array $input The array to recurse through
     * @return string The array printed to a (cleaner) formatted string
     */
    private function printArrayRecursive(array $input):string{
        $outputString="[";
        while(current($input)!==false){
            $thisKey=key($input);
            $thisValue=current($input);
            if(!is_numeric($thisKey)){
                $outputString .= $this->resolveObjectToString($thisKey) . " => ";
            }
            if(is_array($thisValue)){
                $outputString.= $this->printArrayRecursive($thisValue);
            }else{
                $outputString .= $this->resolveObjectToString($thisValue);
            }

            $outputString.=(next($input)!==false ? ", ":"]");
        }
        return $outputString;
    }

    /**
     * Return an array of only the plain-text symbol identifiers
     * @return array list of symbol identifiers
     */
    private function getSymbols():array{
        return array_keys($this->symbolTable);
    }

    /**
     * Iterate through the symbol table and perform a find-and-replace on the rawMessage property
     * @return string a fully-processed string, symbols having been replaced with resolved values
     */
    private function performReplacement():string{
        $outputMessage=$this->rawMessage;
        $resolvedSymbols=$this->resolveSymbols();
        foreach ($this->getSymbols() as $symbol) {
            $templates=$this->getPossibleSymbols($symbol);
            $outputMessage=str_replace($templates,$resolvedSymbols[$symbol],$outputMessage);
        }
        return $outputMessage;
    }

    /**
     * Resolve each value to a string form
     * @param mixed $object the value to resolve to string form
     * @return string
     */
    private function resolveObjectToString($object):string
    {
        //if the symbolTable contains a callable reference, try to resolve it to scalar data
        if(is_callable($object,false,$methodToCall)){

            //set up a resolutions counter. if we can't resolve after so many tries, give up
            $callableCounter=0;

            //begin a loop that will continue resolving until the result is not a callable reference.
            do{
                $resolved=$object();
                if(is_callable($resolved)){
                    $object=$resolved;
                    $callableCounter++;
                }else{
                    $object=$resolved;
                    break;
                }
            }while($callableCounter < self::CALLABLE_COUNTER_MAX);

            //if the $object is still a callable reference after n tries, remove this symbol from the symbolTable
            //and move on to resolving the rest of the list.
            if(is_callable($object,false)){
                return "";
            }
        }
        if(is_object($object)){
            $object=
                $this->objectIsStringable($object)
                    ? (string)$object
                    : "[".$object::class."]";
        }
        if(is_array($object)){
            return $this->printArrayRecursive($object);
        }
        if(is_bool($object)){
            $object=$object?"true":"false";
        }
        if(null===$object) $object="";
        return $object;
    }
}
