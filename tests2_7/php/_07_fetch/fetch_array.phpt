--TEST--
cubrid_fetch_array
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect_with_url($connect_url, $user, $passwd);
cubrid_execute($conn,"drop table if exists fetch_arrary_tb");
cubrid_execute($conn,"CREATE TABLE fetch_arrary_tb(c1 string, c2 char(20), c3 int, c4 double, c5 time, c6 date, c7 TIMESTAMP,c8 bit, c9 numeric(13,4),c10 clob,c11 blob);");
cubrid_execute($conn,"insert into fetch_arrary_tb values('string111111','char11111',1,11.11,TIME '02:10:00',DATE '08/14/1977', TIMESTAMP '08/14/1977 5:35:00 pm',B'1',432341.4321, CHAR_TO_CLOB('This is a Dog'), BIT_TO_BLOB(X'000001'))");
cubrid_execute($conn,"insert into fetch_arrary_tb(c1,c2,c3,c4) values('string2222','char22222',2,11.11)");
cubrid_execute($conn,"insert into fetch_arrary_tb(c5,c6,c7,c8,c9) values(TIME '00:00:00', DATE '2008-10-31',TIMESTAMP '10/31/2013',B'1',513254.3143513)");
cubrid_execute($conn,"insert into fetch_arrary_tb(c10,c11) values(CHAR_TO_CLOB('This is a Dog2'), BIT_TO_BLOB(X'000010'))");


print("#####positive example#####\n");
$req1 = cubrid_execute($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from fetch_arrary_tb;");
if (!$req1) {
    printf("req1 [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
   $row1 = cubrid_fetch_array($req1);
   var_dump($row1);
   
}
cubrid_close_prepare($req1);

$req2=cubrid_execute($conn,"select c1,c2,c3,c4,c5 from fetch_arrary_tb where c3=2");
if (!$req2) {
    printf("req2 [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
   $row2 = cubrid_fetch_array($req2, CUBRID_NUM);
   printf("%s,%s,%d,%f,%s\n",$row2[0],$row2[1],$row2[2],$row2[3],$row2[4]);
   cubrid_close_prepare($req2);
}

$req3=cubrid_query("select c5,c6,c7,c8,c9 from fetch_arrary_tb where c9 = 513254.3144",$conn);
if (!$req3) {
    printf("req3 [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
   $row3 = cubrid_fetch_array($req3, CUBRID_ASSOC);
   printf("%s,%s,%s,%s,%f\n",$row3["c5"],$row3["c6"],$row3["c7"],$row3["c8"],$row3["c9"]);
   cubrid_close_prepare($req3);
}

$req4= cubrid_query("select CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from fetch_arrary_tb order by c1 ",$conn);
while($row4 = cubrid_fetch_array($req4, CUBRID_OBJECT)){
   var_dump($row4);
}
cubrid_close_prepare($req4);

print("\n\n#####negative example#####\n");
$sql3="select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from fetch_arrary_tb where c3>10;";
$req5=cubrid_execute($conn,$sql3);
if(false==$req5){
   printf("[001]execute [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   $row5 = cubrid_fetch_array($req5);
   if(false==$row5){
      printf("[001]fetch_array [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
   }else{
      print("[001] fetch_array success\n");
      var_dump($row5);
   }
}
cubrid_close_prepare($req5);

$req6=cubrid_execute($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from fetch_arrary_tb;");
$row6 = cubrid_fetch_array($req6,CUBRID_NUMM);
if(is_null($row6)){
      printf("[002] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
   }else{
      print("[002] fetch success\n");
      var_dump($row6);
}
cubrid_close_prepare($req6);

$req7=cubrid_execute($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from fetch_arrary_tb;");
$row7 = cubrid_fetch_array($req7,CUBRID_ASSOCC);
if(is_null($row7)){
      printf("[003] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
   }else{
      print("[003] fetch_array success\n");
      var_dump($row7);
}
cubrid_close_request($req7);

$req8=cubrid_execute($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from fetch_arrary_tb;");
$row8 = cubrid_fetch_array($req8,CUBRID_OBJECTT);
if(is_null($row8)){
      printf("[004] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
   }else{
      print("[004] fetch_array success\n");
      var_dump($row8);
}
cubrid_close_prepare($req8);

cubrid_disconnect($conn);

print "Finished!";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
array(22) {
  [0]=>
  string(12) "string111111"
  ["c1"]=>
  string(12) "string111111"
  [1]=>
  string(20) "char11111           "
  ["c2"]=>
  string(20) "char11111           "
  [2]=>
  string(1) "1"
  ["c3"]=>
  string(1) "1"
  [3]=>
  string(19) "11.1099999999999994"
  ["c4"]=>
  string(19) "11.1099999999999994"
  [4]=>
  string(8) "02:10:00"
  ["c5"]=>
  string(8) "02:10:00"
  [5]=>
  string(10) "1977-08-14"
  ["c6"]=>
  string(10) "1977-08-14"
  [6]=>
  string(19) "1977-08-14 17:35:00"
  ["c7"]=>
  string(19) "1977-08-14 17:35:00"
  [7]=>
  string(2) "80"
  ["c8"]=>
  string(2) "80"
  [8]=>
  string(11) "432341.4321"
  ["c9"]=>
  string(11) "432341.4321"
  [9]=>
  string(13) "This is a Dog"
  ["clob_to_char(c10)"]=>
  string(13) "This is a Dog"
  [10]=>
  string(6) "000001"
  ["blob_to_bit(c11)"]=>
  string(6) "000001"
}
string2222,char22222           ,2,11.110000,
00:00:00,2008-10-31,2013-10-31 00:00:00,80,513254.314400
object(stdClass)#1 (2) {
  ["clob_to_char(c10)"]=>
  NULL
  ["blob_to_bit(c11)"]=>
  NULL
}
object(stdClass)#2 (2) {
  ["clob_to_char(c10)"]=>
  string(14) "This is a Dog2"
  ["blob_to_bit(c11)"]=>
  string(6) "000010"
}
object(stdClass)#1 (2) {
  ["clob_to_char(c10)"]=>
  string(13) "This is a Dog"
  ["blob_to_bit(c11)"]=>
  string(6) "000001"
}
object(stdClass)#2 (2) {
  ["clob_to_char(c10)"]=>
  NULL
  ["blob_to_bit(c11)"]=>
  NULL
}


#####negative example#####
[001]fetch_array [0] 

Notice: Use of undefined constant CUBRID_NUMM - assumed 'CUBRID_NUMM' in %s on line %d

Warning: cubrid_fetch_array() expects parameter 2 to be integer, string given in %s on line %d
[002] [0] 

Notice: Use of undefined constant CUBRID_ASSOCC - assumed 'CUBRID_ASSOCC' in %s on line %d

Warning: cubrid_fetch_array() expects parameter 2 to be integer, string given in %s on line %d
[003] [0] 

Notice: Use of undefined constant CUBRID_OBJECTT - assumed 'CUBRID_OBJECTT' in %s on line %d

Warning: cubrid_fetch_array() expects parameter 2 to be integer, string given in %s on line %d
[004] [0] 
Finished!
