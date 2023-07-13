# Message Formatter
## replace {symbols} in strings with any data type.


### Basic Use:
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

### Add and Remove Symbols
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
### Optionals
Optional inclusions in the final string can be designated using square brackets