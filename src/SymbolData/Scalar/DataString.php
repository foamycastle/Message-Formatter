<?php

namespace FoamyCastle\Utils\MessageFormatter\SymbolData\Scalar;

use FoamyCastle\Utils\MessageFormatter\SymbolData\Scalar;

final class DataString extends Scalar
{
    public StringEncoding $encoding;
    public function __construct(string $data)
    {
        $this->encoding=new StringEncoding($data);
        $this->_data=$data;
    }
    public function __toString(): string
    {
        return $this->_data;
    }

    function getType(): string
    {
        return 'string';
    }
    function toEncoding(string $toEncoding):string
    {
        return $this->encoding->toEncoding($this,$toEncoding);
    }

    function isEncoding(?array $tryTheseEncodings=null):string|false
    {
        return mb_detect_encoding($this->_data,$tryTheseEncodings);
    }
}