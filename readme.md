# Message Formatter
### replace {symbols} in strings with any data type.


#### Basic Use:
```php
$message = "I have some {symbols} that I need {replaced}."
$symbolTable = [
    'symbols'=>'light bulbs',
    'replaced'=>function(){
        return "turned on";
    }
];
```
Cast to String:
```php
$replace = new MessageFormatter($message,$symbolTable);
echo $replace;

//output: I have some light bulbs that I need turned on.
```
getMessage() Method:
```php
$replace = new MessageFormatter($message,$symbolTable);
echo $replace->getMessage();

//output: I have some light bulbs that I need turned on.
```
Invokable:
```php
$replace = new MessageFormatter();
echo $replace($message,$symbolTable);

//output: I have some light bulbs that I need turned on.
```
#### Symbol Templates
User has the ability to create different symbol templates. The default symbol template is ```"{symbol}"``` User can add their own template by calling the ```addTemplate``` method.
```php
//create new symbol templates by adding the
//beginning and ending of the symbol

$formatter= new MessageFormatter();

$formatter->addTemplate("[[","]]");

$message="You might catch a [[cold]]";
$symbolTable=[
    'cold'=>'football'
];

echo $formatter($message,$symbolTable);
//output: You might catch a football
```
Remove the template later if necessary:
```php
$formatter->removeTemplate("[[","]]");
```
#### Add and Remove Symbols
User can add and remove symbols with ```addSymbol()``` and ```removeSymbol()``` methods.
```php
$formatter->addSymbol("Cherry Pie",3.14);
```
-OR-
```php
$formatter->addSymbol(['Cherry Pie',3.14]);
//other elements in an array like this will be ignored
//if the first[0] element is not a string, the array will be ignored
```
-OR-
```php
$formatter->addSymbol(['Cherry Pie'=>3.14]);
echo $formatter("Why don't you have some {Cherry Pie}");
//output: Why don't you have some 3.14
```
Now remove the symbol
```php
$formatter->removeSymbol("Cherry Pie");
echo $formatter;
//output: Why don't you have some {Cherry Pie}
```