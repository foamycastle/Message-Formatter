<?php

namespace FoamyCastle\Utils\MessageFormatter\SymbolData;

use FoamyCastle\Utils\MessageFormatter\SymbolData;

final class DataArray extends SymbolData
{
    public const INITIATOR='[';
    public const TERMINATOR=']';
    public const SEPARATOR=', ';
    public const ASSIGNMENT=' -> ';

    private string $initiator;
    private string $terminator;
    private string $separator;
    private string $assignment;

    public function __construct(array $data)
    {
        $this->_data=$data;
    }

    public function __toString(): string
    {
        $output=$this->prettyOutput($this->_data);
        unset($this->initiator,$this->terminator,$this->separator,$this->assignment);
        return $output;
    }
    private function prettyOutput(array $input):string
    {
        $initiator=$this->initiator ?? self::INITIATOR;
        $terminator=$this->terminator ?? self::TERMINATOR;
        $separator=$this->separator ?? self::SEPARATOR;
        $assignment=$this->assignment ?? self::ASSIGNMENT;
        $outputString= $initiator;
        while(false!==current($input)){
            $key=key($input);
            $value=current($input);
            if(is_scalar($value)){
                if(is_bool($value)){
                    $value=$value?"true":"false";
                }
                if(is_string($value)){
                    $value="'$value'";
                }
                if(is_numeric($value)){
                    $value=(string)$value;
                }
            }
            if(is_array($value)){
                $value=$this->prettyOutput($value);
            }
            $outputString.=$key.$assignment.$value;
            if(false!==next($input)){
                $outputString.=$separator;
            }
        }
        return $outputString.$terminator;
    }
    public function withBounds(string $initiator,string $terminator):self
    {
        $this->initiator=$initiator;
        $this->terminator=$terminator;
        return $this;
    }
    public function withSeparator(string $separator):self
    {
        $this->separator=$separator;
        return $this;
    }
    public function withAssign(string $assignment):self
    {
        $this->assignment=$assignment;
        return $this;
    }
}