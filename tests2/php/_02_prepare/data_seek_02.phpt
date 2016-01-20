--TEST--
cubrid_data_seek for APIS-132
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

printf("#####negative testing#####\n");
$req1 = cubrid_execute($conn, "SELECT * FROM seek_tb");

//offset is large than range
$mov2=cubrid_data_seek($req1,5);
if(FALSE == $mov2){
   printf("[002]Expect false [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
   printf("[002]Move success\n");
   $result = cubrid_fetch_row($req1);
   var_dump($result);
}

//offset is less than 0
$mov3=cubrid_data_seek($req1,-1);
if(FALSE == $mov3){
   printf("[003]Expect false [%d] [%s]\n",cubrid_error_code(), cubrid_error_msg());
}else{
   printf("[003]Move success\n");
   $result = cubrid_fetch_row($req1);
   var_dump($result);
}
cubrid_close_request($req1);


cubrid_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####negative testing#####

Warning: Error: CCI, -5, Invalid cursor position in %s on line %d
[002]Expect false [-5] [Invalid cursor position]

Warning: Error: CCI, -5, Invalid cursor position in %s on line %d
[003]Expect false [-5] [Invalid cursor position]
Finished!
