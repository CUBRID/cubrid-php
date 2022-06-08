--TEST--
cubrid_field_flag
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
//table contains all kinds of type
include_once("connect.inc");
$conn = cubrid_connect_with_url($connect_url, $user, $passwd);
cubrid_execute($conn,"drop table if exists flag1_tb;");
cubrid_execute($conn,"CREATE TABLE flag1_tb(c1 string primary key , c2 char(20) not null , c3 int unique key auto_increment, c4 double default 22.22, c5 time default TIME '23:59:59', c6 date, c7 TIMESTAMP default TIMESTAMP  '2038-01-19 12:14:07',c8 bit, c9 numeric(13,4));");
cubrid_execute($conn,"insert into flag1_tb values('string111111','char11111',1,11.11,TIME '02:10:00',DATE '1977-08-14', TIMESTAMP '1977-08-14 5:35:00 pm',B'1',432341.4321)");

print("#####positive example#####\n");
$result=cubrid_execute($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9 from flag1_tb");
var_dump(cubrid_fetch_row($result) );
$col_num = cubrid_num_cols($result);

for($i = 0; $i < $col_num; $i++) {
   printf("%-30s %s\n", cubrid_field_name($result, $i), cubrid_field_flags($result, $i)); 
}

print("\n\n#####negative example#####\n");
$field_result=cubrid_field_flags($result, 11);
if(false == $field_result){
   printf("[001]Expect false value [%d] [%s] \n",cubrid_error_code(),cubrid_error_msg());
}elseif(-1 == $field_result){
   printf("NO Expect -1 value\n");
}else{
   printf("flags: %s\n",$field_result);
}

$field_result2=cubrid_field_flags($result, -1);
if(false == $field_result){
   printf("[001]Expect false value [%d] [%s] \n",cubrid_error_code(),cubrid_error_msg());
}elseif(-1 == $field_result){
   printf("NO Expect -1 value\n");
}else{
   printf("flags: %s\n",$field_result);
}


cubrid_close_request($result);
cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
array(9) {
  [0]=>
  string(12) "string111111"
  [1]=>
  string(20) "char11111           "
  [2]=>
  string(1) "1"
  [3]=>
  string(19) "11.109999999999999%d"
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
c1                             not_null primary_key unique_key
c2                             not_null
c3                             unique_key auto_increment
c4                             
c5                             
c6                             
c7                             timestamp
c8                             
c9                             


#####negative example#####

Warning: Error: CCI, -20013, Column index is out of range in %s on line %d
[001]Expect false value [-20013] [Column index is out of range] 

Warning: Error: CCI, -20013, Column index is out of range in %s on line %d
[001]Expect false value [-20013] [Column index is out of range] 
Finished!
