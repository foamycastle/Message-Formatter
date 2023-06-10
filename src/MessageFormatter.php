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
     * An array that contains symbol templates. The default symbol template is created at instantiation but other
     * symbol templates can be added after the fact.
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
    public function addSymbol(array|string $symbol, $value=null):self{
        if(is_array($symbol)){
            reset($symbol);
            if(is_string($symbol[0])) {
                $this->symbolTable[$symbol[0]] = $symbol[1];
            }
            if(is_string(key($symbol))){
                $key=key($symbol);
                $this->symbolTable[$key]=$symbol[$key];
            }
        }elseif(is_string($symbol)&&null!==$value) {
            $this->symbolTable[$symbol] = $value;
        }
        return $this;
    }
    public function removeSymbol(string $symbol):self{
        if(isset($this->symbolTable[$symbol])){
            unset($this->symbolTable[$symbol]);
        }
        return $this;
    }
    public function getMessage():string{
        return $this->performReplacement();
    }
    public function setMessage(string $message):self{
        $this->rawMessage=$message;
        return $this;
    }

    private function getPossibleSymbols(string $symbol):array{

        $templates= $this->symbolTemplates;
        $outputArray=[];
        foreach ($templates as $template) {
            $outputArray[]=str_replace('%s',$symbol,$template);
        }
        return $outputArray;
    }

    private function resolveSymbols():array
    {
        $outputArray=[];
        foreach ($this->symbolTable as $symbol=>$symbolData) {

            //if the symbolTable contains a callable reference, try to resolve it to scalar data
            if(is_callable($symbolData,false,$methodToCall)){

                //set up a resolutions counter. if we can't resolve after so many tries, give up
                $callableCounter=0;

                //begin a loop that will continue resolving until the result is not a callable reference.
                do{
                    $resolved=$symbolData();
                    if(is_callable($resolved)){
                        $symbolData=$resolved;
                        $callableCounter++;
                        continue;
                    }else{
                        $symbolData=$resolved;
                        break;
                    }
                }while($callableCounter < self::CALLABLE_COUNTER_MAX);

                //if the $symbolData is still a callable reference after n tries, remove this symbol from the symbolTable
                //and move on to resolving the rest of the list.
                if(is_callable($symbolData,false)){
                    unset($symbolData);
                    continue;
                }
            }
            if(is_object($symbolData)){
                $symbolData=
                    $this->objectIsStringable($symbolData)
                        ? (string)$symbolData
                        : "[".$symbolData::class."]";
            }
            if(is_array($symbolData)){
                $outputArray[$symbol]=$this->printArrayRecursive($symbolData);
                continue;
            }
            if(is_bool($symbolData)){
                $symbolData=$symbolData?"true":"false";
            }

            $outputArray[$symbol]=(string)$symbolData;

        }
        return $outputArray;
    }
    private function objectIsStringable(object $data):bool{
        $implement=class_implements($data,false);
        $hasStringMethod=method_exists($data,'__toString');
        return in_array(\Stringable::class,$implement)||$hasStringMethod;
    }
    private function printArrayRecursive(array $input):string{
        $outputString="";
        do{
            $thisInput=current($input);
            $thisKey=key($thisInput);
            $thisValue=$thisInput[$thisKey];
            if(is_array($thisValue)){
                $outputString.= "[$thisKey => ".$this->printArrayRecursive($thisValue)."]";
            }else {
                $outputString .= "[$thisKey => $thisValue]";
            }
            $outputString.=(count($input)>0 ? ", ":"");
        }while(next($input));
        return $outputString;
    }
    private function getSymbols():array{
        return array_keys($this->symbolTable);
    }
    private function performReplacement():string{
        $outputMessage=$this->rawMessage;
        $resolvedSymbols=$this->resolveSymbols();
        foreach ($this->getSymbols() as $symbol) {
            $templates=$this->getPossibleSymbols($symbol);
            $outputMessage=str_replace($templates,$resolvedSymbols[$symbol],$outputMessage);
        }
        return $outputMessage;
    }
}