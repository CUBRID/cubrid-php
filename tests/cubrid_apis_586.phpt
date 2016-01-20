--TEST--
cubrid_version
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

cubrid_execute($conn, 'DROP TABLE IF EXISTS bit1_tb');
cubrid_execute($conn,"create table bit1_tb(a1 bit(8))");
cubrid_execute($conn,"insert into bit1_tb values(B'00001010')");
 
$req1=cubrid_execute($conn,"select * from bit1_tb"); 
if ($req1) {
      $res=cubrid_fetch($req1);
      print_r($res);  
      cubrid_close_prepare($req1); 
}
 
// Test Point 2: test insert bit value by cubrid_bind
cubrid_execute($conn, 'DROP TABLE IF EXISTS bit2_tb');
cubrid_execute($conn,"create table bit2_tb(a2 bit(8))");
 
$req2 = cubrid_prepare($conn, 'INSERT INTO bit2_tb VALUES(?)');
if(!$tmp=cubrid_bind($req2, 1,"10100000",'bit')){
   printf("[%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}
cubrid_execute($req2);
$req2 = cubrid_execute($conn, "SELECT * FROM bit2_tb");
$result = cubrid_fetch_assoc($req2);
var_dump($result);
cubrid_close_prepare($req2);
cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
Array
(
    [0] => 0A
    [a1] => 0A
)
array(1) {
  ["a2"]=>
  string(2) "A0"
}
done!
