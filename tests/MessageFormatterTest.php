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
    public function testBlankSymbolsInOptionals()
    {
        $message = "This is a [message with {optional} {parts}] blank message.";
        $symbols=[
            'optional'=>'',
            'parts'=>''
        ];
        $formatter=new MessageFormatter($message,$symbols);
        self::assertEquals("This is a  blank message.",(string)$formatter);

    }
    public function testSymbolsInOptionals()
    {
        $message = "This is a [message with {optional} {parts}].";
        $symbols=[
            'optional'=>'lame',
            'parts'=>'limbs'
        ];
        $formatter=new MessageFormatter($message,$symbols);
        self::assertEquals("This is a message with lame limbs.",(string)$formatter);

    }
    public function testManyOptionals()
    {
        $message = "This is a [message with {optional} {parts}] [blank {message}].";
        $symbols=[
            'optional'=>'that',
            'parts'=>'stupid',
            'message'=>'look on your face'
        ];
        $formatter=new MessageFormatter($message,$symbols);
        self::assertEquals("This is a message with that stupid blank look on your face.",(string)$formatter);

    }
    public function testManyOptionalsSomeFullSomeBlank()
    {
        $message = "This is a [message with {optional} {parts}][\][blank {message}.]";
        $symbols=[
            'optional'=>'that',
            'parts'=>'stupid',
            'message'=>''
        ];
        $formatter=new MessageFormatter($message,$symbols);
        self::assertEquals("This is a message with that stupid",(string)$formatter);

    }

}
