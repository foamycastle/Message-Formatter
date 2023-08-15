<?php

namespace FoamyCastle\Utils\MessageFormatter\SymbolData\DataObject;

use FoamyCastle\Utils\MessageFormatter\SymbolData\DataObject;

class ObjectCall extends DataObject
{
    private string $callableMethod;
    private array $args;
    public function __construct(object $object,string $method,array $args=[])
    {
        parent::__construct($object);
        $this->callableMethod=$method;
        $this->args=$args;
    }

    public function __toString(): string
    {
        $this->{$this->callableMethod}(...$this->args);
        return $this->returnedValue;
    }
}