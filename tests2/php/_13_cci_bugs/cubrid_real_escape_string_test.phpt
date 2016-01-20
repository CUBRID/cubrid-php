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
cubrid_execute($conn, "DROP TABLE IF EXISTS cubrid_test");
cubrid_execute($conn, "CREATE TABLE cubrid_test (id int, t varchar(20))");

$unescaped1='\\';
$escaped1=cubrid_real_escape_string($unescaped1,$conn);
cubrid_execute($conn, "INSERT INTO cubrid_test (id,t) VALUES(1,'$escaped1')");
$req1 = cubrid_execute($conn, "SELECT * FROM cubrid_test where id=1 ");
while($row = cubrid_fetch_assoc($req1)){
   var_dump($row);
}
cubrid_free_result($req1);

$unescaped2="\\";
cubrid_execute($conn, "INSERT INTO cubrid_test (id,t) VALUES(2,'$unescaped2')");
$req2 = cubrid_execute($conn, "SELECT * FROM cubrid_test where id=2 ");
while($row = cubrid_fetch_assoc($req2)){
   var_dump($row);
}
cubrid_free_result($req2);
cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
array(2) {
  ["id"]=>
  string(1) "1"
  ["t"]=>
  string(1) "\\"
}
array(2) {
  ["id"]=>
  string(1) "2"
  ["t"]=>
  string(1) "\"
}
Finished!
