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
cubrid_execute($conn, 'DROP TABLE IF EXISTS bit1_tb');
cubrid_execute($conn, 'DROP TABLE IF EXISTS bit2_tb');
cubrid_execute($conn, 'DROP TABLE IF EXISTS bit3_tb');
$sql = <<<EOD
CREATE TABLE bit1_tb(a1 bit(8));
EOD;
cubrid_execute($conn,$sql);

cubrid_execute($conn,"create table bit2_tb(a2 bit(8))");
cubrid_execute($conn,"insert into bit2_tb values(B'1010'),(0xaa)");

printf("#####select from bit2_tb #####\n");
$req1=cubrid_execute($conn,"select * from bit2_tb"); 
if ($req1) {
      $res=cubrid_fetch($req1);
      print_r($res);  
      cubrid_close_prepare($req1); 
}

printf("\n\n#####select from bit1_tb #####\n");
$req2 = cubrid_prepare($conn, 'INSERT INTO bit1_tb VALUES(?)');
if(!$tmp=cubrid_bind($req2, 1,"B'1010'",'bit')){
   printf("[%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}
cubrid_execute($req2);
$req3 = cubrid_execute($conn, "SELECT * FROM bit1_tb");
$result = cubrid_fetch_assoc($req3);
var_dump($result);
cubrid_close_prepare($req3);

printf("\n\n#####select from bit3_tb #####\n");
cubrid_execute($conn,"create table bit3_tb(a3 bit(8))");
$req4 = cubrid_prepare($conn, 'INSERT INTO bit3_tb VALUES(?)');
if(!$tmp=cubrid_bind($req4, 1,B'1010','bit')){
   printf("[%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}
cubrid_execute($req4);
$req5 = cubrid_execute($conn, "SELECT * FROM bit3_tb");
$result5 = cubrid_fetch_assoc($req5);
var_dump($result5);
cubrid_close_prepare($req5);

cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--XFAIL--
http://jira.cubrid.org/browse/APIS-409 
--EXPECTF--
#####select from bit2_tb #####
Array
(
    [0] => A0
    [a2] => A0
)


#####select from bit1_tb #####
array(1) {
  ["a1"]=>
  string(2) "A0"
}


#####select from bit3_tb #####
array(1) {
  ["a3"]=>
  string(2) "A0"
}
Finished!
