--TEST--
cubrid_result
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

cubrid_execute($conn, 'DROP TABLE IF EXISTS result_tb');
$sql ="CREATE TABLE result_tb(id int, name varchar(10), address string default NULL, phone char(10))";
cubrid_execute($conn,$sql);
cubrid_execute($conn,"insert into result_tb values(1,'name1','string1','1111-11-11'),(2,'name2','string2','2222-22-22'),(3,'name3','string3','3333-33-33'),(4,'name4','string4','4444-44-44'),(5,'name5','string5','5555-55-55')");
cubrid_execute($conn,"insert into result_tb(id,name) values(6,'name6')");

$req = cubrid_execute($conn, "SELECT * FROM result_tb ");
$result0 = cubrid_result($req, 0, "result_tb.id");
var_dump($result0);

$result1 = cubrid_result($req, 1, "result_tb.address");
var_dump($result1);

$result2 = cubrid_result($req, 2, "result_tb.phonee");
if(FALSE == $result2){
   printf("[002]Expect false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[002]Get result success\n");
   var_dump($result2);
}

$result3 = cubrid_result($req,5, "result_tb.phone");
if(FALSE == $result3){
   printf("[003]Expect false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[003]Get result success\n");
   var_dump($result3);
}

$result4 = cubrid_result($req,3, "result_tb");
if(FALSE == $result4){
   printf("[004]Expect false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[004]Get result success\n");
   var_dump($result4);
}

$result5 = cubrid_result($req,3, "result_tb.phone");
if(FALSE == $result5){
   printf("[005]Expect false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[005]Get result success\n");
   var_dump($result5);
}
cubrid_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
string(1) "1"
string(7) "string2"

Warning: Error: CCI, -20013, Column index is out of range in %s on line %d
[002]Expect false [-20013] [Column index is out of range]
[003]Expect false [0] []

Warning: Error: CCI, -20013, Column index is out of range in %s on line %d
[004]Expect false [-20013] [Column index is out of range]
[005]Get result success
string(10) "4444-44-44"
Finished!
