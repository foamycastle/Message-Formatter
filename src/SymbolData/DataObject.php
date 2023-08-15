<?php

namespace FoamyCastle\Utils\MessageFormatter\SymbolData;

use FoamyCastle\Utils\MessageFormatter\SymbolData;

abstract class DataObject extends SymbolData
{
    protected \ReflectionObject $reflectionObject;
    protected mixed $returnedValue=null;
    public function __construct(object $object)
    {
        $this->_data=$object;
        $this->reflectionObject=new \ReflectionObject($this->_data);
    }

    public function __call(string $name, array $arguments)
    {
        if($this->reflectionObject->hasMethod($name)){
            $reflectionMethod=new \ReflectionMethod($this->_data,$name);

            $newClosure=$reflectionMethod->isStatic()
                ? $reflectionMethod->getClosure(null)
                : $reflectionMethod->getClosure($this->_data);

            $this->returnedValue=call_user_func($newClosure,...$arguments);
            return;
        }
        $this->returnedValue=null;
    }
}