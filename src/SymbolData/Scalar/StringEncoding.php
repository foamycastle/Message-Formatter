<?php

namespace FoamyCastle\Utils\MessageFormatter\SymbolData\Scalar;

final class StringEncoding
{
    private string $currentEncoding;
    private bool $mbStringAvailable=false;
    public function __construct(string &$data)
    {
        if(!extension_loaded('mbstring')){
            $this->currentEncoding="ISO-8859-1";
            return;
        }
        $this->mbStringAvailable=true;
        $detect=mb_detect_encoding($data);
        if(false===$detect){
            $this->currentEncoding="ISO-8859-1";
        }else{
            $this->currentEncoding=$detect;
        }
    }
    public function toEncoding(string $data,string $encoding):string
    {
        if(!$this->mbStringAvailable) return $data;
        if(!mb_check_encoding($data,$encoding)) return $data;
        return mb_convert_encoding($data,$encoding,$this->currentEncoding);
    }
}