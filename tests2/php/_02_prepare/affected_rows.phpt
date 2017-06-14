--TEST--
cubrid_affected_rows
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

cubrid_execute($conn, 'DROP TABLE IF EXISTS php_tb');
$sql = <<<EOD
CREATE TABLE php_tb(id int, char_t varchar(30));
EOD;
cubrid_execute($conn,$sql);

print("#####correct #####\n");
$sql2="insert into php_tb values(";
for($i = 0; $i <10000; $i++){
   $sql2 = $sql2 . $i . ",".$i . "),(";
}
$sql2= $sql2 . 10000 . "," . 10000 . ");";
cubrid_execute($conn,$sql2);
$affected_num = cubrid_affected_rows();
printf("Rows inserted: %d\n", $affected_num);

$sql3="update php_tb set id=id+100000 where id <5000";
cubrid_execute($conn,$sql3);
$affected_num = cubrid_affected_rows();
printf("Rows updated: %d\n", $affected_num);

cubrid_execute($conn,"delete from php_tb");
$affected_num = cubrid_affected_rows();
printf("Rows deleted: %d\n", $affected_num);


printf("\n\n#####error execute#####\n");
$res=cubrid_execute($conn,"insert into php_tb values(1,'1')");
if(is_null($affected_num = cubrid_affected_rows($res))){
   printf("[001] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("Rows: %d\n", $affected_num);
}

cubrid_execute($conn,"insert into php_tb values(1,'1')");
cubrid_execute($conn,"select * from php_tb");
if(-1 == ($affected_num = cubrid_affected_rows())){
   printf("[002] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("Rows: %d\n", $affected_num);
}


cubrid_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####correct #####
Rows inserted: 10001
Rows updated: 5000
Rows deleted: 10001


#####error execute#####
Rows: 1

Warning: Error: CLIENT, -30002, Invalid API call in %s on line %d
[002] [-30002] Invalid API call
Finished!
