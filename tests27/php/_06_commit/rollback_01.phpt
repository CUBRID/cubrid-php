--TEST--
cubrid_rollback
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");

$conn = cubrid_connect_with_url($connect_url, $user, $passwd);

@cubrid_execute($conn, "DROP TABLE IF EXISTS roll_tb");
cubrid_query('CREATE TABLE roll_tb(a int)');
cubrid_query('INSERT INTO roll_tb(a) VALUE(1)');

cubrid_close($conn);
$conn = cubrid_connect_with_url($connect_url, $user, $passwd);

cubrid_set_autocommit($conn, CUBRID_AUTOCOMMIT_FALSE);

$req = cubrid_query('SELECT * FROM roll_tb');
$res = cubrid_fetch_array($req, CUBRID_ASSOC);

var_dump($res);

cubrid_query('DROP TABLE IF EXISTS roll_tb');

cubrid_rollback($conn);

cubrid_close($conn);
$conn = cubrid_connect_with_url($connect_url, $user, $passwd);

$req = cubrid_query('SELECT * FROM roll_tb');
$res = cubrid_fetch_array($req, CUBRID_ASSOC);

var_dump($res);

cubrid_close($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
array(1) {
  ["a"]=>
  string(1) "1"
}

Warning: cubrid_query(): supplied resource is not a valid CUBRID Connect resource in %s on line %d

Warning: cubrid_fetch_array() expects parameter 1 to be resource, boolean given in %s on line %d
NULL

Warning: cubrid_close(): supplied resource is not a valid CUBRID Connect resource in %s on line %d
done!
