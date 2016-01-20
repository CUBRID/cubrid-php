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
cubrid_execute($conn, 'DROP TABLE IF EXISTS time2_tb');

cubrid_execute($conn,"CREATE TABLE time2_tb(c1 int, c2 time, c3 date, c4 TIMESTAMP);");
$req2 = cubrid_prepare($conn, "INSERT INTO time2_tb VALUES(1,?,?,?);");
if(false == ($tmp=cubrid_bind($req2, 1, '25:22:60','time'))){
   printf("bind '25:22:60','time' failed \n");
}else{
   printf("bind time success\n");
}

cubrid_bind($req2, 1, '02:22:59','time');
cubrid_bind($req2, 2, '2012/03/02', 'date');
cubrid_bind($req2, 3, '1977/08/14 1:36:10 PM', 'timestamp');
cubrid_execute($req2);

$req3= cubrid_execute($conn, "SELECT * FROM time2_tb");
if($req3){
   $result = cubrid_fetch_assoc($req3);
   var_dump($result);
   cubrid_close_prepare($req3);
}

cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--XFAIL--
http://jira.cubrid.org/browse/APIS-479
--EXPECTF--
bind time success
array(4) {
  ["c1"]=>
  string(1) "1"
  ["c2"]=>
  string(8) "02:22:59"
  ["c3"]=>
  string(10) "2012-03-02"
  ["c4"]=>
  string(19) "1977-08-14 01:36:10 pm"
}
Finished!
