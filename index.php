<?php

include 'vendor/autoload.php';

use FoamyCastle\Utils\MessageFormatter\Message;
use FoamyCastle\Utils\MessageFormatter\SymbolTable;

$time=microtime(true);
$message=new Message("This is[ an {example} of][ a {tool} that] creates {complex} strings at [{forget_this}]runtime.");

SymbolTable::import([
    'tool'=>'library',
    'example'=>'awesome example',
    'complex'=>function(){return "fun";}
]);

echo $message.PHP_EOL;
echo "time: ".(string)(microtime(true)-$time).PHP_EOL;



/*
echo $message;
*/