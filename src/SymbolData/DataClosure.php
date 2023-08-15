<?php

namespace FoamyCastle\Utils\MessageFormatter\SymbolData;

use FoamyCastle\Utils\MessageFormatter\SymbolData;

final class DataClosure extends SymbolData
{
    private array $args;
    public function __construct(\Closure $data,array ...$args)
    {
        $this->_data=$data;
        $this->args=$args;
    }

    public function __toString(): string
    {
        $runThisFunction=\Closure::bind(
            $this->_data,
            null,
            null,
        );
        return SymbolData::New(empty($this->args)?$runThisFunction():$runThisFunction(...$this->args));
    }

    public function withArgs(...$args):self
    {
        $this->args=$args;
        return $this;
    }
}