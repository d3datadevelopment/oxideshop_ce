--TEST--
oe-console test
--ARGS--
list
--FILE--
<?php
require __DIR__ . '/../../../../../bin/oe-console';
?>
--EXPECTF_EXTERNAL--
Fixtures/output-oe-console.txt