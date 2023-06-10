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
    public function test__invoke()
    {
        $message='this is a {good} message';
        $symbols=[
            'good'=>function(){
                return 'buttfucked';
            }
        ];
        $formatter=new MessageFormatter();
        self::assertIsString((string)$formatter($message,$symbols));
    }
    public function testGetMessage()
    {
        $message='this is a {good} message';
        $symbols=[
            'good'=>function(){
                return 'buttfucked';
            }
        ];
        $formatter=new MessageFormatter($message,$symbols);
        self::assertIsString($formatter($message,$symbols)->getMessage());
    }
    public function testAddSymbol(){
        $message='this is a {symbol1} message. this is a {symbol2},this is a {symbol3}';
        $symbols=[
            'good'=>function(){
                return 'buttfucked';
            }
        ];
        $formatter=new MessageFormatter($message);
        $formatter
            ->addSymbol(['symbol1'=>'gorp'])
            ->addSymbol(['symbol2','fuckup'])
            ->addSymbol('symbol3','dildo');
        self::assertGreaterThan(0,stripos($formatter,'gorp'));
        self::assertGreaterThan(0,stripos($formatter,'fuckup'));
        self::assertGreaterThan(0,stripos($formatter,'dildo'));
        self::assertFalse(stripos($formatter,'buttfucked'));
    }
    public function testAddTemplate()
    {
        $message='this is a {symbol1} message. this is a [dope:,this is a [cheese:';
        $symbols=[
            'symbol1'=>function(){
                return 'buttfucked';
            }
        ];
        $formatter=new MessageFormatter($message,$symbols);
        $formatter
            ->addTemplate("[",":")
            ->addSymbol('dope','fucking cool')
            ->addSymbol('cheese','corn');
        self::assertGreaterThan(0,stripos($formatter,'fucking cool'));
        self::assertGreaterThan(0,stripos($formatter,'corn'));
        self::assertGreaterThan(0,stripos($formatter,'buttfucked'));
    }
}
