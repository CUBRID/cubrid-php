--TEST--
cubrid_insert_id
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

@cubrid_execute($conn, "DROP TABLE cubrid_test");
cubrid_execute($conn, "CREATE TABLE cubrid_test (d int AUTO_INCREMENT(1, 2), e numeric(38, 0) AUTO_INCREMENT(11111111111111111111111111111111111111, 2), t varchar)");

for ($i = 0; $i < 10; $i++) {
    cubrid_execute($conn, "INSERT INTO cubrid_test (t) VALUES('cubrid_test')");
}

$id_list = cubrid_insert_id("cubrid_test");
var_dump($id_list);

$id_list = cubrid_insert_id("code");
var_dump($id_list);

cubrid_execute($conn, "SELECT * FROM cubrid_test");
$id_list = cubrid_insert_id("cubrid_test");
var_dump($id_list);

cubrid_execute($conn, "CREATE TABLE cubrid_test (d int AUTO_INCREMENT(1, 2), e numeric(39, 0) AUTO_INCREMENT(1, 2), t varchar)");

cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
array(2) {
  ["d"]=>
  string(2) "19"
  ["e"]=>
  string(38) "11111111111111111111111111111111111129"
}
array(0) {
}
int(0)

Warning: Error: DBMS, -493, Syntax: Precision (39) too large. Maximum precision is 38.  in %s on line %d
done!
