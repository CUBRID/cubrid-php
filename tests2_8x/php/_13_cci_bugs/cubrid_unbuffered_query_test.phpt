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
cubrid_execute($conn, 'DROP TABLE IF EXISTS unbuffered_tb');
cubrid_execute($conn,"CREATE TABLE unbuffered_tb(id int, name varchar(10))");
cubrid_execute($conn,"insert into unbuffered_tb values(1,'name1')");
$res=cubrid_unbuffered_query("SELECT * FROM unbuffered_tb ; ", $conn);
if (!$res) {
    printf("[006] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    var_dump(cubrid_fetch_assoc($res));
}
cubrid_free_result($res);

cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
array(2) {
  ["id"]=>
  string(1) "1"
  ["name"]=>
  string(5) "name1"
}
Finished!
