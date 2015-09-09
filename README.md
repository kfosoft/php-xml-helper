# PHP XML Helper
##### Class for encode xml string to array or object & encode array to SimpleXMLElement 
## Installation

Installation with Composer

Either run
~~~
    php composer.phar require --prefer-dist kfosoft/php-xml-helper:"*"
~~~
or add in composer.json
~~~
    "require": {
        ...
        "kfosoft/php-xml-helper":"*"
    }
~~~

Well done!

## Example encode
~~~
$xml = (new XML())->encode(array(
        'book' => array(
            1,
            'page' => 1,
        ),
        'book1' => array(
            'attribute:test' => 2,
            'attribute:test2' => 'testValue',
            'page' => 1,
            'page1' => 'testValue',
        ),
), 'document')
~~~
##### Result:
~~~
<?xml version="1.0"?>
<document><book><item>1</item><page>1</page></book><book1 test="2" test2="testValue"><page>1</page><page1>testValue</page1></book1></document>
~~~

## Example decode
~~~
$xml = '<?xml version="1.0"?>
<document><book><item>1</item><page>1</page></book><book1 test="2" test2="testValue"><page>1</page><page1>testValue</page1></book1></document>';

$xml = (new XML())->decode($xml);

or

$xml = (new XML())->decode($xml,true);
~~~
##### Result:
~~~
To array:
array(2) {
  'book' =>
  array(2) {
    [0] =>
    string(1) "1"
    'page' =>
    string(1) "1"
  }
  'book1' =>
  array(3) {
    'attribute:' =>
    array(2) {
      'test' =>
      string(1) "2"
      'test2' =>
      string(9) "testValue"
    }
    'page' =>
    string(1) "1"
    'page1' =>
    string(9) "testValue"
  }
}


To object:
class stdClass#62 (2) {
  public $book =>
  array(2) {
    [0] =>
    string(1) "1"
    'page' =>
    string(1) "1"
  }
  public $book1 =>
  array(3) {
    'attribute:' =>
    array(2) {
      'test' =>
      string(1) "2"
      'test2' =>
      string(9) "testValue"
    }
    'page' =>
    string(1) "1"
    'page1' =>
    string(9) "testValue"
  }
}
~~~

Enjoy, guys!
