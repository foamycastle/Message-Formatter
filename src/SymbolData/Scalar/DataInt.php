<?php

namespace FoamyCastle\Utils\MessageFormatter\SymbolData\Scalar;

use FoamyCastle\Utils\MessageFormatter\SymbolData\Scalar;

final class DataInt extends Scalar
{

    public function __construct(int $data)
    {
        $this->_data=$data;
    }

    public function __toString(): string
    {
        return (string)$this->_data;
    }

    function getType(): string
    {
        return 'int';
    }
}