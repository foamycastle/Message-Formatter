<?php

namespace FoamyCastle\Utils\MessageFormatter\SymbolData\Scalar;

use FoamyCastle\Utils\MessageFormatter\SymbolData\Scalar;

final class DataBool extends Scalar
{
    public function __construct(bool $data)
    {
        $this->_data=$data;
    }
    public function getType(): string
    {
        return 'bool';
    }
    public function __toString(): string
    {
        return ($this->_data?"True":"False");
    }
}