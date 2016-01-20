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
cubrid_execute($conn, 'DROP TABLE IF EXISTS time_tb');
$sql = <<<EOD
CREATE TABLE time_tb(c1 string, time_tb time,date_t date);
EOD;
cubrid_execute($conn,$sql);

//date time type
$req = cubrid_prepare($conn, "INSERT INTO time_tb VALUES('time date test',?,?);");
cubrid_bind($req, 1, '02:22:22','time');
cubrid_bind($req, 2, '08/14/1977','date');
cubrid_execute($req);

$req2= cubrid_execute($conn, "SELECT * FROM time_tb where c1 like 'time%';");
if($req2){
   $result = cubrid_fetch_assoc($req2);
   var_dump($result);
}
cubrid_close_prepare($req2);

cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
array(3) {
  ["c1"]=>
  string(14) "time date test"
  ["time_tb"]=>
  string(8) "02:22:22"
  ["date_t"]=>
  string(10) "1977-08-14"
}
Finished!
