--TEST--
cubrid_set
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php

include_once("connect.inc");

$conn = cubrid_connect_with_url($connect_url);
if (!$conn) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
    exit(1);
}

cubrid_execute($conn, 'DROP TABLE IF EXISTS set_tbl_int');

$sql_stmt_create = "CREATE TABLE set_tbl_int ( col_1 set(int), col_2 set(string));";
$sql_stmt_insert = "INSERT INTO set_tbl_int VALUES ({1,2,3,4},{'a','de'});";

cubrid_execute($conn, $sql_stmt_create);
cubrid_execute($conn, $sql_stmt_insert);

$req = cubrid_execute($conn, 'select * from set_tbl_int');
$result = cubrid_fetch_assoc($req);
var_dump($result);

cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
array(2) {
  ["col_1"]=>
  array(4) {
    [0]=>
    string(1) "1"
    [1]=>
    string(1) "2"
    [2]=>
    string(1) "3"
    [3]=>
    string(1) "4"
  }
  ["col_2"]=>
  array(2) {
    [0]=>
    string(1) "a"
    [1]=>
    string(2) "de"
  }
}
done!
