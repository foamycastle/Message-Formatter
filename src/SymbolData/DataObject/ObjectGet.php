<?php

namespace FoamyCastle\Utils\MessageFormatter\SymbolData\DataObject;

use FoamyCastle\Utils\MessageFormatter\SymbolData;
use FoamyCastle\Utils\MessageFormatter\SymbolData\DataArray;
use FoamyCastle\Utils\MessageFormatter\SymbolData\DataObject;
use FoamyCastle\Utils\MessageFormatter\SymbolData\Scalar;

class ObjectGet extends DataObject
{
    private array $properties;
    private array $methods;
    public function __construct(object $object)
    {
        parent::__construct($object);
        $this->properties=$this->getProperties();
        $this->methods=$this->getMethodReturns();
    }

    public function __toString(): string
    {
        return (string)(new DataArray(array_merge($this->properties,$this->methods)));
    }
    private function getProperties():array
    {
        $properties=$this->reflectionObject->getProperties();
        $outputArray=[];
        foreach ($properties as $property) {
            $name=$property->getName();
            $outputArray[$name]=$this->getProperty($name);
        }
        return $outputArray;
    }
    private function getMethodReturns():array
    {
        $methods=$this->reflectionObject->getMethods();
        $outputArray=[];
        foreach ($methods as $method){
            $outputArray[$method->getName()."()"]=$this->getMethod($method->getName());
        }
        return $outputArray;
    }

    public function getProperty(string $name):SymbolData
    {
        if($this->reflectionObject->hasProperty($name)) {
            return SymbolData::New($this->reflectionObject->getProperty($name)->getValue($this->_data));
        }
        return SymbolData::New(null);
    }
    public function getMethod(string $name):SymbolData
    {
        if ($this->reflectionObject->hasMethod($name)) {
            $method=$this->reflectionObject->getMethod($name);
            $requiresArgs = $method->getNumberOfRequiredParameters() > 0;
            $isMagicMethod = str_starts_with($method->getName(), "__");
            if (!$requiresArgs && !$isMagicMethod) {
                return SymbolData::New($method->invoke($this->_data));
            }
        }
        return SymbolData::New(null);
    }
}