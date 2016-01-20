--TEST--
cubrid_autocommit
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

if (cubrid_get_autocommit($conn)) {
    printf("Autocommit is ON.\n");
} else {
    printf("Autocommit is OFF.");
}

@cubrid_execute($conn, "DROP TABLE if exists commit3_tb");
cubrid_query('CREATE TABLE commit3_tb(a int)');
cubrid_query('INSERT INTO commit3_tb(a) VALUE(1)');

cubrid_close($conn);
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

$req = cubrid_query('SELECT * FROM commit3_tb');
$res = cubrid_fetch_array($req, CUBRID_ASSOC);

var_dump($res);

cubrid_set_autocommit($conn, CUBRID_AUTOCOMMIT_FALSE);
cubrid_query('UPDATE commit3_tb SET a=2');

cubrid_close($conn);
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

$req = cubrid_query('SELECT * FROM commit3_tb');
$res = cubrid_fetch_array($req, CUBRID_ASSOC);

var_dump($res);

cubrid_query('DROP TABLE commit3_tb');

cubrid_close($conn);
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

$req = cubrid_query('SELECT * FROM commit3_tb');

cubrid_close($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
Autocommit is ON.
array(1) {
  ["a"]=>
  string(1) "1"
}
array(1) {
  ["a"]=>
  string(1) "1"
}

Warning: Error: DBMS, -493, Syntax: Unknown class "commit3_tb". select * from commit3_tb%s in %s on line %d
done!
