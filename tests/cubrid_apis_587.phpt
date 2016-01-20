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
cubrid_execute($conn,"insert into bit1_tb values(B'1010')");

$req1=cubrid_execute($conn,"select * from bit1_tb where a1 = B'1010'"); 
if ($req1) {
      $res=cubrid_fetch($req1);
      print_r($res);  
      cubrid_close_prepare($req1); 
}

// Test Point: test the fetch result with cubrid_bind
$req2=cubrid_prepare($conn,"select * from bit1_tb where a1 = ?"); 
cubrid_bind($req2, 1, "00001010", "bit");
cubrid_execute($req2);

$result = cubrid_fetch_assoc($req2);
var_dump($result);

cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
Array
(
    [0] => A0
    [a1] => A0
)
bool(false)
done!
