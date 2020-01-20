--TEST--
cubrid_bind
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
cubrid_execute($conn, "DROP TABLE if exists current_oid2_tb");
cubrid_execute($conn, "CREATE TABLE current_oid2_tb(a int, b varchar(10) )");
cubrid_execute($conn, "INSERT INTO current_oid2_tb values(1,'varchar1'),(2,'varchar2'),(3,'varchar3'),(4,'varchar4')");
$req = cubrid_execute($conn, "SELECT * FROM current_oid2_tb order by a ", CUBRID_INCLUDE_OID);

//move cursor to the third record
cubrid_move_cursor($req, 3, CUBRID_CURSOR_FIRST);
$oid = cubrid_current_oid($req);
printf("The third record's oid: %s\n",$oid);
$attr = cubrid_get($conn, $oid, "b");
var_dump($attr);

cubrid_move_cursor($req,4, CUBRID_CURSOR_FIRST);
$oid2 = cubrid_current_oid($req);
printf("\n\nThe fourth record's oid: %s\n",$oid2);
$attr2 = cubrid_get($conn, $oid2, "b");
var_dump($attr2);

cubrid_close_request($req);


cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
The third record's oid: %s
string(8) "varchar3"


The fourth record's oid: %s
string(8) "varchar4"
Finished!
