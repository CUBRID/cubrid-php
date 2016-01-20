--TEST--
cubrid_data_seek
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

cubrid_execute($conn, 'DROP TABLE IF EXISTS seek_tb');
$sql ="CREATE TABLE seek_tb(id int, name varchar(10))";
cubrid_execute($conn,$sql);
cubrid_execute($conn,"insert into seek_tb values(1,'name1'),(2,'name2'),(3,'name3'),(4,'name4'),(5,'name5')");

$req = cubrid_execute($conn, "SELECT * FROM seek_tb");
printf("#####positive testing#####\n");
cubrid_data_seek($req, 0);
$result = cubrid_fetch_row($req);
var_dump($result);

cubrid_data_seek($req, 1);
$result = cubrid_fetch_row($req);
var_dump($result);

cubrid_data_seek($req,3);
$result = cubrid_fetch_row($req);
var_dump($result);

cubrid_close_request($req);

printf("#####negative testing#####\n");
$req1 = cubrid_execute($conn, "SELECT * FROM seek_tb");
//passing three parameters
$mov1=cubrid_data_seek($req1, 1,1);
if(FALSE == $mov1){
   printf("[001]Expect false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[001]Move success\n");
   $result = cubrid_fetch_row($req1);
   var_dump($result);
}

//offset is large than range
$mov2=cubrid_data_seek($req1,5);
if(FALSE == $mov2){
   printf("[002]Expect false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[002]Move success\n");
   $result = cubrid_fetch_row($req1);
   var_dump($result);
}

//offset is less than 0
$mov3=cubrid_data_seek($req1,-1);
if(FALSE == $mov3){
   printf("[003]Expect false [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
   printf("[003]Move success\n");
   $result = cubrid_fetch_row($req1);
   var_dump($result);
}
cubrid_close_request($req1);

//query result is no data
$req2 = cubrid_execute($conn, "SELECT * FROM seek_tb where id > 10");
$mov4=cubrid_data_seek($req2, 0);
if(FALSE == $mov4){
   printf("[004]Expect false [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
   printf("[004]Move success\n");
   $result = cubrid_fetch_row($req2);
   var_dump($result);
}
cubrid_close_request($req2);


cubrid_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive testing#####
array(2) {
  [0]=>
  string(1) "1"
  [1]=>
  string(5) "name1"
}
array(2) {
  [0]=>
  string(1) "2"
  [1]=>
  string(5) "name2"
}
array(2) {
  [0]=>
  string(1) "4"
  [1]=>
  string(5) "name4"
}
#####negative testing#####

Warning: cubrid_data_seek() expects exactly 2 parameters, 3 given in %s on line %d
[001]Expect false [0] []

Warning: Error: CCI, -5, Invalid cursor position in %s on line %d
[002]Expect false [-5] [Invalid cursor position]

Warning: Error: CCI, -5, Invalid cursor position in %s on line %d
[003]Expect false [-5] [Invalid cursor position]

Warning: cubrid_data_seek(): Number of rows is NULL.
 in %s on line %d
[004]Expect false [0] []
Finished!
