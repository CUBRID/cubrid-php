--TEST--
cubrid_schema CUBRID_SCH_ATTR_PRIVILEGE
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn = cubrid_connect($host, $port, $db,  $user, $passwd);
//cubrid_execute($conn, "DROP TABLE if exists col2_get_tb");
cubrid_execute($conn, "CREATE TABLE col2_get_tb(a int AUTO_INCREMENT, b set(int), c list(int,varchar(10)), d char(10))");
//cubrid_execute($conn, "CREATE TABLE col2_get_tb(a int AUTO_INCREMENT, b set(int), c list(varchar(10)), d char(10))");
cubrid_execute($conn, "INSERT INTO col2_get_tb(a, b, c, d) VALUES(1, {1,2,3}, {11,22,33,'varchar1','varchar2'}, 'a')");
//cubrid_execute($conn, "INSERT INTO col2_get_tb(a, b, c, d) VALUES(1, {1,2,3}, {'varchar1','varchar2','varchar3'}, 'a')");
$req = cubrid_execute($conn, "SELECT * FROM col2_get_tb", CUBRID_INCLUDE_OID);

cubrid_move_cursor($req, 1, CUBRID_CURSOR_FIRST);
$oid = cubrid_current_oid($req);

$attr = cubrid_col_get($conn, $oid, "c");
var_dump($attr);

$size = cubrid_col_size($conn, $oid, "c");
var_dump($size);

cubrid_close_request($req);


cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
array(3) {
  [0]=>
  string(2) "11"
  [1]=>
  string(2) "22"
  [2]=>
  string(2) "33"
  [3]=>
  string(8) "varchar1"
  [4]=>
  string(8) "varchar2"
  [5]=>
  string(8) "varchar3"
}
int(6)
Finished!
