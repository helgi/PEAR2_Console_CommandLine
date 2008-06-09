--TEST--
Test for PEAR2_Console_CommandLine::parse() method (special cases 1).
--SKIPIF--
<?php if(php_sapi_name()!='cli') echo 'skip'; ?>
--ARGS--
-t -- -f - --float=1.2 foo 2>&1
--FILE--
<?php

require_once 'Console/CommandLine.php' ;
require_once __DIR__ . DIRECTORY_SEPARATOR . 'tests.inc.php';

try {
    $parser = buildParser1();
    $result = $parser->parse();
    var_dump($result);
} catch (PEAR2_Console_CommandLine_Exception $exc) {
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
    NULL
    ["int"]=>
    int(1)
    ["float"]=>
    float(1)
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
    string(2) "-f"
    ["multiple"]=>
    array(3) {
      [0]=>
      string(1) "-"
      [1]=>
      string(11) "--float=1.2"
      [2]=>
      string(3) "foo"
    }
  }
  ["command_name"]=>
  bool(false)
  ["command"]=>
  bool(false)
}
