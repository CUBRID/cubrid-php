--TEST--
php info
--SKIPIF--
--FILE--
<?php
//phpinfo();
echo phpversion();
?>
--CLEAN--
--EXPECTF--
8.%s
