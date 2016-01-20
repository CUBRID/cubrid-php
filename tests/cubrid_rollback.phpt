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

$tmp = NULL;
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

@cubrid_execute($conn, "DROP TABLE rollback_test");
cubrid_query('CREATE TABLE rollback_test(a int)');
cubrid_query('INSERT INTO rollback_test(a) VALUE(1)');

$req = cubrid_query('SELECT * FROM rollback_test');
$res = cubrid_fetch_array($req, CUBRID_ASSOC);

var_dump($res);

cubrid_rollback($conn);

$req = cubrid_query('SELECT * FROM rollback_test');

cubrid_close($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
array(1) {
  ["a"]=>
  string(1) "1"
}

Warning: Error: DBMS, -493, Syntax: Unknown class "rollback_test". select * from rollback_test in %s on line %d
done!
