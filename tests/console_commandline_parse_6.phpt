--TEST--
Test for Console_CommandLine::parse() method (errors 3).
--SKIPIF--
<?php if(php_sapi_name()!='cli') echo 'skip'; ?>
--ARGS--
--float=1.2 foo 2>&1
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tests.inc.php';

try {
    $parser = buildParser1();
    $result = $parser->parse();
} catch (PEAR2_Console_CommandLine_Exception $exc) {
    $parser->displayError($exc->getMessage());
}

?>
--EXPECT--
Error: You must provide at least 2 arguments.
Type "some_program -h" to get help.
