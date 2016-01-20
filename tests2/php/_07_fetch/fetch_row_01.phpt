--TEST--
cubrid_fetch_row and cubrid_data_seek
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect_with_url($connect_url, $user, $passwd);
cubrid_execute($conn,"drop table if exists row_tb");
cubrid_execute($conn,"CREATE TABLE row_tb(c1 string, c2 char(20), c3 int, c4 double, c5 time, c6 date, c7 TIMESTAMP,c8 bit, c9 numeric(13,4),c10 clob,c11 blob);");
cubrid_execute($conn,"insert into row_tb values('string111111','char11111',1,11.11,TIME '02:10:00',DATE '08/14/1977', TIMESTAMP '08/14/1977 5:35:00 pm',B'1',432341.4321, CHAR_TO_CLOB('This is a Dog'), BIT_TO_BLOB(X'000001'))");
cubrid_execute($conn,"insert into row_tb(c1,c2,c3,c4) values('string2222','char22222',2,11.11)");
cubrid_execute($conn,"insert into row_tb(c3,c5,c6,c7,c8,c9) values(3,TIME '00:00:00', DATE '2008-10-31',TIMESTAMP '10/31',B'1',513254.3143513)");
cubrid_execute($conn,"insert into row_tb(c3,c10,c11) values(4,CHAR_TO_CLOB('This is a Dog2'), BIT_TO_BLOB(X'000010'))");


print("#####positive example#####\n");
$req1 = cubrid_execute($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from row_tb order by c3;");
if (!$req1) {
    printf("req1 [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
   $row1 = cubrid_fetch_row($req1);
   var_dump($row1);
   cubrid_data_seek($req1, 1);
   $row = cubrid_fetch_row($req1);
   printf("%s, %s, %d ,%f, %s, %s, %s, %s, %f, %s, %s\n", $row[0], $row[1], $row[2], $row[3],$row[4],$row[5],$row[6],$row[7],$row[8],$row[9],$row[10]);
   cubrid_data_seek($req1, 3);
   $row2 = cubrid_fetch_row($req1);
   printf("%s, %s, %d ,%f, %s, %s, %s, %s, %f, %s, %s\n", $row2[0], $row2[1], $row2[2], $row2[3],$row2[4],$row2[5],$row2[6],$row2[7],$row2[8],$row2[9],$row2[10]);
}
cubrid_close_prepare($req1);

print("\n\n#####data_seek nagetive example#####\n");
$req2 = cubrid_execute($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from row_tb order by c3;");
$seek1=cubrid_data_seek($req2,-1);
if(false== $seek1){
   printf("[001] Expecting FALSE, got %s,%s\n", gettype($seek1), $seek1);
   printf("[001] got [%d] [%s]\n",cubrid_error_code(), cubrid_error_msg());
}else{
   $row = cubrid_fetch_row($req2);
   var_dump($row);
}
cubrid_close_request($req2);

$req2 = cubrid_execute($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from row_tb order by c3;");
$seek2=cubrid_data_seek($req2,6);
if(false== $seek2){
   printf("[002] Expecting FALSE, got %s,%s\n", gettype($seek2), $seek2);
   printf("[002] got [%d] [%s]\n",cubrid_error_code(), cubrid_error_msg());
}else{
   $row2 = cubrid_fetch_row($req2);
   var_dump($row2);
}
cubrid_close_request($req2);

$req3 = cubrid_execute($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from row_tb order by c3;");
$seek3=cubrid_data_seek($req3);
if(false== $seek3){
   printf("[003] Expecting FALSE, got [%d] [%s]\n",cubrid_error_code(), cubrid_error_msg());
}else{
   $row3 = cubrid_fetch_row($req3);
   var_dump($row3);
}
cubrid_close_request($req3);

print("\n\n#####fetch_row nagetive example#####\n");
$req4 = cubrid_execute($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from row_tb order by c3;");
if (!$req4) {
    printf("req4 [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
   $row4 = cubrid_fetch_row($req4);
   $row_result1=$row4[-1];
   if($row_result1){
      print($row_result1);
   }else{
      printf("[004] Expecting FALSE, got [%d] [%s]\n",cubrid_error_code(), cubrid_error_msg());
   }
   $row_result2=$row4[11];
   if($row_result2){
      print($row_result2);
   }else{
      printf("[005] Expecting FALSE, got [%d] [%s]\n",cubrid_error_code(), cubrid_error_msg());
   }
}
cubrid_close_prepare($req4);

$req5= cubrid_execute($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from row_tb where c3 >100;");
if (!$req5) {
    printf("req5 [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
   $row5 = cubrid_fetch_row($req5);
   if(false==$row5){
      printf("[006] Expecting FALSE, got [%d] [%s]\n",cubrid_error_code(), cubrid_error_msg());
   }else{
      printf("%s, %s, %d ,%f, %s, %s, %s, %s, %f, %s, %s\n", $row5[0], $row5[1], $row5[2], $row5[3],$row5[4],$row5[5],$row5[6],$row5[7],$row5[8],$row5[9],$row5[10]);
   }
}
cubrid_close_prepare($req5);

cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
array(11) {
  [0]=>
  string(12) "string111111"
  [1]=>
  string(20) "char11111           "
  [2]=>
  string(1) "1"
  [3]=>
  string(19) "11.1099999999999994"
  [4]=>
  string(8) "02:10:00"
  [5]=>
  string(10) "1977-08-14"
  [6]=>
  string(19) "1977-08-14 17:35:00"
  [7]=>
  string(2) "80"
  [8]=>
  string(11) "432341.4321"
  [9]=>
  string(13) "This is a Dog"
  [10]=>
  string(6) "000001"
}
string2222, char22222           , 2 ,11.110000, , , , , 0.000000, , 
, , 4 ,0.000000, , , , , 0.000000, This is a Dog2, 000010


#####data_seek nagetive example#####

Warning: Error: CCI, -5, Invalid cursor position in %s on line %d
[001] Expecting FALSE, got boolean,
[001] got [-5] [Invalid cursor position]

Warning: Error: CCI, -5, Invalid cursor position in %s on line %d
[002] Expecting FALSE, got boolean,
[002] got [-5] [Invalid cursor position]

Warning: cubrid_data_seek() expects exactly 2 parameters, 1 given in %s on line %d
[003] Expecting FALSE, got [0] []


#####fetch_row nagetive example#####

Notice: Undefined offset: -1 in %s on line %d
[004] Expecting FALSE, got [0] []

Notice: Undefined offset: 11 in %s on line %d
[005] Expecting FALSE, got [0] []
[006] Expecting FALSE, got [0] []
Finished!
