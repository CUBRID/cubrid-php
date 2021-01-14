--TEST--
cubrid_lob_get cubrid_lob_export cubrid_lob_size
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
//testing for clob type
include "connect.inc";
$tmp = NULL;
$fp1=fopen('php/_09_lob/clob1.txt','r');
$fp2=fopen('php/_09_lob/clob2.txt','r');
$fp3=fopen('php/_09_lob/chinese.txt','r');
$fp4=fopen('php/_09_lob/clob_large.txt','r');
$conn=cubrid_connect($host, $port, $db, $user, $passwd);
cubrid_execute($conn,"drop table if exists get1_tb");
cubrid_execute($conn,"CREATE TABLE get1_tb(a int AUTO_INCREMENT, f clob)");
$cubrid_req = cubrid_prepare($conn, "insert into get1_tb (f) values (?)");
cubrid_bind($cubrid_req, 1, $fp1, "clob");
cubrid_execute($cubrid_req);

$cubrid_req = cubrid_prepare($conn, "insert into get1_tb (f) values (?)");
cubrid_bind($cubrid_req, 1, $fp2, "clob");
cubrid_execute($cubrid_req);

$cubrid_req = cubrid_prepare($conn, "insert into get1_tb (f) values (?)");
cubrid_bind($cubrid_req, 1, $fp3, "clob");
cubrid_execute($cubrid_req);

$cubrid_req = cubrid_prepare($conn, "insert into get1_tb (f) values (?)");
cubrid_bind($cubrid_req, 1, $fp4, "clob");
cubrid_execute($cubrid_req);
fclose($fp1);
fclose($fp2);
fclose($fp3);
fclose($fp4);


print("#####positive example#####\n");
$lobs = cubrid_lob_get($conn, "SELECT f FROM get1_tb ");
cubrid_lob_export($conn, $lobs[0], "php/_09_lob/f1.txt");
cubrid_lob_export($conn, $lobs[1], "php/_09_lob/f2.txt");
cubrid_lob_export($conn, $lobs[2], "php/_09_lob/f3.txt");
cubrid_lob_export($conn, $lobs[3], "php/_09_lob/f4.txt");
if(cubrid_lob_size($lobs[0]) != filesize("php/_09_lob/clob1.txt")) {
    printf(" Clob01 data export error.\n");
}else{
    print("Clob01 data export success\n");
    printf("%d--size: %d\n", __LINE__, cubrid_lob_size($lobs[0]));
}

if(cubrid_lob_size($lobs[1]) != filesize("php/_09_lob/clob2.txt")) {
    printf("Clob1 data export error.\n");
}else{
    print("Clob1 data export success\n");
    printf("%d--size: %d\n",__LINE__, cubrid_lob_size($lobs[1]));
}
if(cubrid_lob_size($lobs[2]) != filesize("php/_09_lob/chinese.txt")) {
    printf("Clob2 data export error.\n");
}else{
    print("Clob2 data export success\n");
    printf("%d--size: %d\n", __LINE__, cubrid_lob_size($lobs[2]));
}
$lob_size=cubrid_lob_size($lobs[3]);
if($lob_size ==filesize("php/_09_lob/clob_large.txt")){
    print("Clob3 data export success.\n");
    printf("%d--size: %d\n", __LINE__, cubrid_lob_size($lobs[3]));
}else{
    print("Clob3 data export error\n");
}
//$lob_size=cubrid_lob_size($lobs[3]);
//printf("clob4.txt's size is %s \n",$lob_size);
cubrid_lob_close($lobs);


print("\n\n#####negative example: lob_get#####\n");
$fp1=fopen('php/_09_lob/clob1.txt','r');
$cubrid_req = cubrid_prepare($conn, "insert into get1_tb (f) values (?)");
cubrid_bind($cubrid_req, 1, $fp1, "blob");
cubrid_execute($cubrid_req);
$tmp = cubrid_lob_get($conn);
if ($tmp == FALSE) {
    printf("[001] Expecting FALSE got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}

$tmp = cubrid_lob_get($conn, NULL);
if ($tmp == false) {
    printf("[002] Expecting boolean/false got [%s] [%s]\n", gettype($tmp), $tmp);
}

$tmp = cubrid_lob_get($conn, "insert into get1_tb(a) values (1)");
if ($tmp == false) {
    printf("[003] Expecting boolean/false got [%s] [%s]\n", gettype($tmp), $tmp);
}

$tmp = cubrid_lob_get($conn, "select a from get1_tb");
if ($tmp == false) {
    printf("[004] Expecting boolean/false got [%s] [%s]\n", gettype($tmp), $tmp);
}else{
   printf("[004] get success\n");
   cubrid_lob_close($lobs);
}

$tmp = cubrid_lob_get($conn, "select f,a from get1_tb");
if ($tmp == false) {
    printf("[005] Expecting boolean/false got [%s] [%s]\n", gettype($tmp), $tmp);
}else{
   printf("[005] get success\n");
   cubrid_lob_close($lobs);
} 


cubrid_commit($conn);
cubrid_disconnect($conn);

//rm export file
unlink("php/_09_lob/f1.txt");
unlink("php/_09_lob/f2.txt");
unlink("php/_09_lob/f3.txt");
unlink("php/_09_lob/f4.txt");
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
Clob01 data export success
43--size: 137
Clob1 data export success
50--size: 620
Clob2 data export success
56--size: 7012
Clob3 data export success.
61--size: 49915


#####negative example: lob_get#####

Warning: Error: DBMS, -494, Semantic: Cannot coerce host var to type clob. %s in %s on line %d

Warning: cubrid_lob_get() expects exactly 2 parameters, 1 given in %s on line %d
[001] Expecting FALSE got [0] []

Warning: Error: DBMS, -424, No statement to execute.%s in %s on line %d
[002] Expecting boolean/false got [boolean] []

Warning: cubrid_lob_get(): Get result info fail or sql type is not select in %s on line %d
[003] Expecting boolean/false got [boolean] []

Warning: cubrid_lob_get(): Column type is not BLOB or CLOB. in %s on line %d
[004] Expecting boolean/false got [boolean] []

Warning: cubrid_lob_get(): More than one columns returned in %s on line %d
[005] Expecting boolean/false got [boolean] []
Finished!
