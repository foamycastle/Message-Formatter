<?php

namespace FoamyCastle\Utils\Test;

use FoamyCastle\Utils\MessageFormatter\MessageFormatter;
use PHPUnit\Framework\TestCase;

class MessageFormatterTest extends TestCase
{

    public function testResolveSymbols()
    {
        $message='this is a {good} message';
        $symbols=[
            'good'=>function(){
                $newDate=new \DateTime('now');
                return $newDate->format(DATE_RFC2822);
            }
        ];
        $formatter=new MessageFormatter($message,$symbols);
        self::assertIsString((string)$formatter);
    }
}
