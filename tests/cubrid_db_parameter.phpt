--TEST--
cubrid_db_parameter
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");

$tmp = NULL;
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

if (!is_null($tmp = @cubrid_get_db_parameter())) {
    printf("[001] Expecting NULL, got %s/%s\n", gettype($tmp), $tmp);
}

$params = cubrid_get_db_parameter($conn);

var_dump($params);

cubrid_close($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
array(4) {
  ["PARAM_ISOLATION_LEVEL"]=>
  int(3)
  ["LOCK_TIMEOUT"]=>
  int(-1)
  ["MAX_STRING_LENGTH"]=>
  int(1073741823)
  ["PARAM_AUTO_COMMIT"]=>
  int(0)
}
done!
