--TEST--
APIS-434, cubrid_insert_id
--SKIPIF--
<?php # vim:ft=php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$tmp = NULL;
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

$id = cubrid_insert_id($conn);
var_dump($id);

@cubrid_execute($conn, "DROP TABLE cubrid_test");
cubrid_execute($conn, "create table cubrid_test (id numeric(38,0) auto_increment(1000000000000, 2), name varchar)");
cubrid_execute($conn, "INSERT INTO cubrid_test (name) VALUES('cubrid_test')");
$id = cubrid_insert_id();
var_dump($id);

cubrid_execute($conn, "SELECT * FROM cubrid_test");
$id = cubrid_insert_id();
var_dump($id);

print "Finish!";
?>
--CLEAN--
--EXPECTF--
bool(false)
string(13) "1000000000000"
int(0)
Finish!
