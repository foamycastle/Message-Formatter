<?php

namespace FoamyCastle\Utils\MessageFormatter\SymbolData\Scalar;

use FoamyCastle\Utils\MessageFormatter\SymbolData\Scalar;

final class DataFloat extends Scalar
{
    public function __construct(float $data)
    {
        $this->_data=$data;
    }
    public function __toString(): string
    {
        return (string)$this->_data;
    }

    function getType(): string
    {
        return 'float';
    }
    function roundUp(int $decimalPlaces):float
    {
        return round($this->_data,$decimalPlaces);
    }

    function roundDown(int $decimalPlaces):float
    {
        return round($this->_data,$decimalPlaces,PHP_ROUND_HALF_DOWN);
    }

}