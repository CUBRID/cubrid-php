--TEST--
cubrid_drop and table contains partiton
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php
printf("negative testing\n");

include_once("connect.inc");
$conn = cubrid_connect_with_url($connect_url, $user, $passwd);
cubrid_execute($conn, "drop table if exists partition_tb");
cubrid_execute($conn, "create table partition_tb(id int ,test_char char(50),test_varchar varchar(2000))");
$alterSql="ALTER TABLE partition_tb PARTITION BY LIST (test_char) (PARTITION p0 VALUES IN ('aaa','bbb','ddd'),PARTITION p1 VALUES IN ('fff','ggg','hhh',NULL),PARTITION p2 VALUES IN ('kkk','lll','mmm') )";
$insertSql="insert into partition_tb values(1,'aaa','aaa')";
$insertSql2="insert into partition_tb values(5,'ggg','ggg')";
cubrid_execute($conn,$alterSql);
cubrid_execute($conn, $insertSql);
cubrid_execute($conn,$insertSql2);

$req = cubrid_execute($conn, "select * from partition_tb where id >10 ", CUBRID_INCLUDE_OID);

$oid = cubrid_current_oid($req);
if (FALSE==$oid){
    printf("Expect false for oid [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    printf("oid: %s\n",$oid);
}
$tmp=cubrid_drop($conn, $oid);
if (FALSE ==$tmp){
    printf("Expect false for cubrid_drop, [%d] [%s] \n",cubrid_error_code(),cubrid_error_msg());
}
else {
    printf("drop success\n");
}

$tmp2=cubrid_drop($conn,$nothisoid);
if (FALSE ==$tmp2){
    printf("[002]Expect false for cubrid_drop, [%d] [%s] \n",cubrid_error_code(),cubrid_error_msg());
}
else {
    printf("drop success\n");
}


cubrid_close_request($req);


cubrid_disconnect($conn);

print "Fished!\n";
?>
--CLEAN--
--EXPECTF--
negative testing

Warning: Error: CAS, -1012, Invalid cursor position in %s on line %d
Expect false for oid [-1012] Invalid cursor position

Warning: Error: CCI, -20, Invalid oid string in %s on line %d
Expect false for cubrid_drop, [-20] [Invalid oid string] 

Notice: Undefined variable: nothisoid in %s on line %d

Warning: Error: CCI, -20, Invalid oid string in %s on line %d
[002]Expect false for cubrid_drop, [-20] [Invalid oid string] 
Fished!
