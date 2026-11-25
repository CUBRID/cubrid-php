--TEST--
cubrid_result for APIS-129
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

cubrid_execute($conn, 'DROP TABLE IF EXISTS tb');
cubrid_execute($conn,"CREATE TABLE tb(id int, name varchar(10), address string default NULL, phone varchar(10))");
cubrid_execute($conn,"insert into tb(id,name,phone) values(6,'name6','NULL')");

$req=cubrid_execute($conn, "SELECT * FROM tb ");
$value=cubrid_result($req,0,'address');
if(is_null($value)){
   printf("[001]Expect null [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(FALSE == $value ){
   printf("[001] No expect false [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
   printf("[001]Get result success\n");
   var_dump($value);
}

$value2=cubrid_result($req,0,2);
if(is_null($value2)){
   printf("[001]Expect null [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(FALSE == $value2 ){
   printf("[001] No expect false [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
   printf("[001]Get result success\n");
   var_dump($value2);
}


printf("#####correct resutl:#####\n");
$result2 = cubrid_result($req,0,1);
var_dump($result2);

$result3 = cubrid_result($req,0,3);
if(FALSE == $result3){
   printf("[002]No expect false [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(is_null($result3)){
   printf("[002]Expect null [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
   printf("[002]Get result success\n");
   var_dump($result3);
}
cubrid_close_request($req);

cubrid_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
[001]Expect null [0] []
[001]Expect null [0] []
#####correct resutl:#####
string(5) "name6"
[002]Get result success
string(4) "NULL"
Finished!
