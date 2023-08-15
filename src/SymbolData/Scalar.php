<?php

namespace FoamyCastle\Utils\MessageFormatter\SymbolData;

use FoamyCastle\Utils\MessageFormatter\SymbolData;
use FoamyCastle\Utils\MessageFormatter\SymbolData\Scalar\DataBool;
use FoamyCastle\Utils\MessageFormatter\SymbolData\Scalar\DataString;
use FoamyCastle\Utils\MessageFormatter\SymbolDataInterface;

abstract class Scalar extends SymbolData implements SymbolDataInterface
{
    public static function New($data):SymbolData
    {
        if(is_bool($data)) return new DataBool($data);
        return new DataString($data);
    }

    public function upper():string
    {
        return strtoupper((string)$this);
    }
    public function lower():string
    {
        return strtolower((string)$this);
    }

}