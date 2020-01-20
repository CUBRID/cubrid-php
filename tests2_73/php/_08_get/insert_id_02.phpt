--TEST--
cubrid_insert_id
--SKIPIF--
<?php 
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");

$tmp = NULL;
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
cubrid_execute($conn, "DROP TABLE if exists insert_tb");
cubrid_execute($conn, "CREATE TABLE insert_tb(a int auto_increment, b varchar(10))");

for($i=1;$i<=10;$i++){
   cubrid_execute($conn,"insert into insert_tb(b) values($i)");
}
$id1 = cubrid_insert_id();
var_dump($id1);
printf("\n\n");

cubrid_execute($conn,"insert into insert_tb values(1,'1')");
$id2 = cubrid_insert_id($conn);
if(FALSE === $id2){
   printf("[002]Return value is false, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}elseif(0 === $id2){
   printf("[002]Return value is 0, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("[002]id: %s\n",$id2);
}

cubrid_execute($conn,"select * from insert_tb");
$id3 = cubrid_insert_id($conn);
if(FALSE === $id3){
   printf("[003]Return value is false, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}elseif(0 === $id3){
   printf("[003]Return value is 0, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("[003]id: %s\n",$id3);
}

cubrid_disconnect($conn);

print "Finishe!\n";
?>
--CLEAN--
--EXPECTF--
string(2) "10"


[002]id: 10
[003]Return value is 0, [0] []
Finishe!
