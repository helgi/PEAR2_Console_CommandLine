--TEST--
Test for Console_CommandLine::parse() method (user argc/argv 1).
--SKIPIF--
<?php if(php_sapi_name()!='cli') echo 'skip'; ?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tests.inc.php';

$argv = array('somename', '-t', '-f', '--float=1.2', 'foo', 'bar');
$argc = count($argv);
try {
    $parser = buildParser1();
    $result = $parser->parse($argc, $argv);
    var_dump($result);
} catch (Console_CommandLine_Exception $exc) {
    $parser->displayError($exc->getMessage());
}

?>
--EXPECT--
object(PEAR2_Console_CommandLine_Result)#21 (4) {
  ["options"]=>
  array(11) {
    ["true"]=>
    bool(true)
    ["false"]=>
    bool(false)
    ["int"]=>
    int(1)
    ["float"]=>
    float(1.2)
    ["string"]=>
    NULL
    ["counter"]=>
    NULL
    ["callback"]=>
    NULL
    ["array"]=>
    NULL
    ["password"]=>
    NULL
    ["help"]=>
    NULL
    ["version"]=>
    NULL
  }
  ["args"]=>
  array(2) {
    ["simple"]=>
    string(3) "foo"
    ["multiple"]=>
    array(1) {
      [0]=>
      string(3) "bar"
    }
  }
  ["command_name"]=>
  bool(false)
  ["command"]=>
  bool(false)
}
