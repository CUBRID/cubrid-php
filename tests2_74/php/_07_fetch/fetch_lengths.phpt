--TEST--
cubrid_fetch_lengths
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect_with_url($connect_url, $user, $passwd);
cubrid_execute($conn,"drop table if exists fetch_length_tb");
cubrid_execute($conn,"CREATE TABLE fetch_length_tb(c1 string, c2 char(20), c3 int, c4 double, c5 time, c6 date, c7 TIMESTAMP,c8 bit, c9 numeric(13,4),c10 clob,c11 blob);");
cubrid_execute($conn,"insert into fetch_length_tb values('string1','char1',1,11.11,TIME '02:10:00',DATE '08/14/1977', TIMESTAMP '08/14/1977 5:35:00 pm',B'1',432341.4321, CHAR_TO_CLOB('This is a Dog'), BIT_TO_BLOB(X'000001'))");
cubrid_execute($conn,"insert into fetch_length_tb(c1,c2,c3,c4) values('string2','char3',2,11.11)");
cubrid_execute($conn,"insert into fetch_length_tb(c5,c6,c7,c8,c9) values(TIME '00:00:00', DATE '2008-10-31',TIMESTAMP '10/31',B'1',513254.3143513)");

print("#####positive example#####\n");
if (!$req = cubrid_query("select c1,c2,c3,c4,c5,c6,c7,c8,c9 from fetch_length_tb order by c1 DESC", $conn)) {
    printf("correct1 [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

$row = cubrid_fetch_row($req);
print_r($row);
$lens = cubrid_fetch_lengths($req);
print_r($lens);

cubrid_move_cursor($req, 2, CUBRID_CURSOR_FIRST);
$result2 = cubrid_fetch_row($req);
var_dump($result2);
print_r(cubrid_fetch_lengths($req));

cubrid_close_request($req);

print("\n\n#####negative example#####\n");
if (!$req2 = cubrid_query("select c1,c2,c3,c4,c5,c6,c7,c8,c9 from fetch_length_tb where c3 > 10", $conn)) {
   printf("negative1 query [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   $row2 = cubrid_fetch_row($req2);
   if(false==$row2){
      printf("negative1 fetch_row [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
   }else{
      var_dump($row2);
      $lens2 = cubrid_fetch_lengths($req2);
      var_dump($lens2);
   }
cubrid_close_request($req2);
}

$lens3 = cubrid_fetch_lengths("no this req");
if(false == $lens3){
   printf("negative2 fetch_lengths [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
   var_dump($lens3);
}



cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
Array
(
    [0] => string2
    [1] => char3               
    [2] => 2
    [3] => 11.1099999999999994
    [4] => 
    [5] => 
    [6] => 
    [7] => 
    [8] => 
)
Array
(
    [0] => 7
    [1] => 20
    [2] => 1
    [3] => 19
    [4] => 0
    [5] => 0
    [6] => 0
    [7] => 0
    [8] => 0
)
array(9) {
  [0]=>
  string(7) "string1"
  [1]=>
  string(20) "char1               "
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
}
Array
(
    [0] => 7
    [1] => 20
    [2] => 1
    [3] => 19
    [4] => 8
    [5] => 10
    [6] => 19
    [7] => 2
    [8] => 11
)


#####negative example#####
negative1 fetch_row [0] 

Warning: cubrid_fetch_lengths() expects parameter 1 to be resource, string given in %s on line %d
negative2 fetch_lengths [0] 
Finished!
