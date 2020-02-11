--TEST--
cubrid_fetch_assoc
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect_with_url($connect_url, $user, $passwd);
cubrid_execute($conn,"drop table if exists assoc_tb");
cubrid_execute($conn,"CREATE TABLE assoc_tb(c1 string, c2 char(20), c3 int, c4 double, c5 time, c6 date, c7 TIMESTAMP,c8 bit, c9 numeric(13,4),c10 clob,c11 blob);");
cubrid_execute($conn,"insert into assoc_tb values('string111111','char11111',1,11.11,TIME '02:10:00',DATE '08/14/1977', TIMESTAMP '08/14/1977 5:35:00 pm',B'1',432341.4321, CHAR_TO_CLOB('This is a Dog'), BIT_TO_BLOB(X'000001'))");
cubrid_execute($conn,"insert into assoc_tb(c1,c2,c3,c4) values('string2222','char22222',2,11.11)");
cubrid_execute($conn,"insert into assoc_tb(c3,c5,c6,c7,c8,c9) values(3,TIME '00:00:00', DATE '2008-10-31',TIMESTAMP '10/31',B'1',513254.3143513)");
cubrid_execute($conn,"insert into assoc_tb(c3,c10,c11) values(4,CHAR_TO_CLOB('This is a Dog2'), BIT_TO_BLOB(X'000010'))");


print("#####positive example#####\n");
$req1 = cubrid_execute($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10) as c10,BLOB_TO_BIT(c11) as c11 from assoc_tb order by c3;");
if (!$req1) {
    printf("req1 [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
   $row1 = cubrid_fetch_assoc($req1);
   var_dump($row1);
   cubrid_data_seek($req1, 1);
   $row = cubrid_fetch_assoc($req1);
   printf("%s, %s, %d ,%f, %s, %s, %s, %s, %f, %s, %s\n", $row["c1"], $row["c2"], $row["c3"], $row["c4"],$row["c5"],$row["c6"],$row["c7"],$row["c8"],$row["c9"],$row["c10"],$row["c11"]);
   cubrid_data_seek($req1, 3);
   $row3 = cubrid_fetch_assoc($req1);
   printf("%s, %s, %d ,%f, %s, %s, %s, %s, %f, %s, %s\n", $row3["c1"], $row3["c2"], $row3["c3"], $row3["c4"],$row3["c5"],$row3["c6"],$row3["c7"],$row3["c8"],$row3["c9"],$row3["c10"],$row3["c11"]);
}
cubrid_close_prepare($req1);


print("\n\n#####fetch_row nagetive example#####\n");
$req4 = cubrid_execute($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from assoc_tb order by c3;");
if (!$req4) {
    printf("req4 [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
   $row4 = cubrid_fetch_assoc($req4);
   $row_result1=$row4["nothiscolumn"];
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

$req5= cubrid_execute($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10),BLOB_TO_BIT(c11) from assoc_tb where c3 >100;");
if (!$req5) {
    printf("req5 [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
   $row5 = cubrid_fetch_assoc($req5);
   if(false==$row5){
      printf("[006] Expecting FALSE, got [%d] [%s]\n",cubrid_error_code(), cubrid_error_msg());
   }else{
      printf("%s, %s, %d ,%f, %s, %s, %s, %s, %f, %s, %s\n", $row5["c1"], $row5["c2"], $row5["c3"], $row5["c4"],$row5["c5"],$row5["c6"],$row5["c7"],$row5["c8"],$row5["c9"],$row5["c10"],$row5["c11"]);
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
  ["c1"]=>
  string(12) "string111111"
  ["c2"]=>
  string(20) "char11111           "
  ["c3"]=>
  string(1) "1"
  ["c4"]=>
  string(19) "11.1099999999999994"
  ["c5"]=>
  string(8) "02:10:00"
  ["c6"]=>
  string(10) "1977-08-14"
  ["c7"]=>
  string(19) "1977-08-14 17:35:00"
  ["c8"]=>
  string(2) "80"
  ["c9"]=>
  string(11) "432341.4321"
  ["c10"]=>
  string(13) "This is a Dog"
  ["c11"]=>
  string(6) "000001"
}
string2222, char22222           , 2 ,11.110000, , , , , 0.000000, , 
, , 4 ,0.000000, , , , , 0.000000, This is a Dog2, 000010


#####fetch_row nagetive example#####

Notice: Undefined index: nothiscolumn in %s on line %d
[004] Expecting FALSE, got [0] []

Notice: Undefined offset: 11 in %s on line %d
[005] Expecting FALSE, got [0] []
[006] Expecting FALSE, got [0] []
Finished!
