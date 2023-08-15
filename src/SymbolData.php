<?php

namespace FoamyCastle\Utils\MessageFormatter;


use FoamyCastle\Utils\MessageFormatter\SymbolData\DataArray;
use FoamyCastle\Utils\MessageFormatter\SymbolData\DataClosure;
use FoamyCastle\Utils\MessageFormatter\SymbolData\DataObject\ObjectCall;
use FoamyCastle\Utils\MessageFormatter\SymbolData\DataObject\ObjectGet;
use FoamyCastle\Utils\MessageFormatter\SymbolData\Scalar;
use PhpParser\Node\Expr\Closure;

abstract class SymbolData implements \Stringable
{
    protected $_data;

    public static function New($data):SymbolData
    {
        if(is_array($data)) {
            if (count($data) == 2) {
                [$object, $method] = $data;
                if (is_object($object) && is_string($method)) {
                    return new ObjectCall($object, $method);
                }
            }
            if (count($data) == 3) {
                [$object, $method, $args] = $data;
                if (is_object($object) && is_string($method) && is_array($args)){
                    if (empty($args)) {
                        return new ObjectCall($object,$method);
                    }
                return new ObjectCall($object, $method, ...$args);
                }
            }
            return new DataArray($data);
        }
        if($data instanceof \Closure) return new DataClosure($data);
        if(is_object($data)) return new ObjectGet($data);
        if(is_null($data)) return new Scalar\DataString("null");
        return Scalar::New($data);
    }


}