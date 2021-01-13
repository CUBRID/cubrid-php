--TEST--
cubrid_lob_get  cubrid_lob_export cubrid_lob_size
--SKIPIF-- 
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
//testing for blob type
include "connect.inc";
$tmp = NULL;
$fp1 = fopen('php/_09_lob/logo1.png', 'rb');
$fp2= fopen('php/_09_lob/logo2.png', 'rb');
$fp3= fopen('php/_09_lob/logo3.png', 'rb');
$conn=cubrid_connect($host, $port, $db, $user, $passwd);
cubrid_execute($conn,"drop table if exists get_tb");
cubrid_execute($conn,"CREATE TABLE get_tb(a int AUTO_INCREMENT,b blob, e blob, f clob)");
$cubrid_req = cubrid_prepare($conn, "insert into get_tb (b,e) values (?,?)");
cubrid_bind($cubrid_req, 1, $fp1, "blob");
cubrid_bind($cubrid_req,2,$fp2, "blob");
cubrid_execute($cubrid_req);

$cubrid_req = cubrid_prepare($conn, "insert into get_tb (e) values (?)");
cubrid_bind($cubrid_req, 1, $fp3, "blob");
cubrid_execute($cubrid_req);
fclose($fp1);
fclose($fp2);
fclose($fp3);

$fp3= fopen('php/_09_lob/logo3.png', 'rb');
$cubrid_req = cubrid_prepare($conn, "insert into get_tb (e) values (?)");
cubrid_bind($cubrid_req, 1, $fp3, "blob");
cubrid_execute($cubrid_req);
fclose($fp3);

print("#####positive example: lob_get lob_export lob_size#####\n");
$lobs = cubrid_lob_get($conn, "SELECT e FROM get_tb ");
cubrid_lob_export($conn, $lobs[0], "php/_09_lob/e1.jpg");
cubrid_lob_export($conn, $lobs[1], "php/_09_lob/e2.jpg");
cubrid_lob_export($conn, $lobs[2], "php/_09_lob/e3.jpg");
if (cubrid_lob_size($lobs[0]) != filesize("php/_09_lob/logo1.png")) {
    printf(" Blob0 data export error.\n");
}else{
    print("Blobo data export successful\n");
}

if (cubrid_lob_size($lobs[1]) != filesize("php/_09_lob/logo3.png")) {
    printf("Blob1 data export error.\n");
}else{
    print("Blob1 data export successful\n");
}
if (cubrid_lob_size($lobs[2]) != filesize("php/_09_lob/logo3.png")) {
    printf("Blob2 data export error.\n");
}else{
    print("Blob2 data export successful\n");
}
cubrid_lob_close($lobs);

print("\n\n#####negative example: lob_get#####\n");
$tmp = cubrid_lob_get($conn);
if ($tmp == FALSE) {
    printf("[001] Expecting FALSE got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}

$tmp = cubrid_lob_get($conn, NULL);
if ($tmp == false) {
    printf("[002] Expecting boolean/false got [%s] [%s]\n", gettype($tmp), $tmp);
}

$tmp = cubrid_lob_get($conn, "insert into get_tb(a) values (1)");
if ($tmp == false) {
    printf("[003] Expecting boolean/false got [%s] [%s]\n", gettype($tmp), $tmp);
}

$tmp = cubrid_lob_get($conn, "select a from get_tb");
if ($tmp == false) {
    printf("[004] Expecting boolean/false got [%s] [%s]\n", gettype($tmp), $tmp);
}else{
   printf("[004] get success\n");
   cubrid_lob_close($lobs);
}

$tmp = cubrid_lob_get($conn, "select a,e from get_tb");
if ($tmp == false) {
    printf("[005] Expecting boolean/false got [%s] [%s]\n", gettype($tmp), $tmp);
}else{
   printf("[005] get success\n");
   cubrid_lob_close($lobs);
}  

print("\n\n#####negative example: lob_export#####\n");
$lobs = cubrid_lob_get($conn, "select e from get_tb ");
$ret = cubrid_lob_export($conn, $lobs[-1], "php/_09_lob/lob_test.png");
if (!$ret) {
    printf("[006] Expect blob export failed. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}
$ret2 = cubrid_lob_export($conn, $lobs[3], "php/_09_lob/lob_test.png");
if (!$ret2) {
    printf("[007] Expect blob export failed. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}

$ret3 = cubrid_lob_export($conn, $lobs[0], "");
if (!$ret3) {
    printf("[008] Expect blob export failed. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[008] Blob export success.\n");
}

$ret4 = cubrid_lob_export($conn, $lobs, "php/_09_lob/009.jpg");
if (!$ret4) {
    printf("[009] Expect blob export failed. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[009] Blob export success.\n");
}

$ret5 = cubrid_lob_export($conn, $lobs[0]);
if (!$ret5) {
    printf("[010] Expect blob export failed. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[010] Blob export success.\n");
}

$ret6 = cubrid_lob_export($conn);
if (!$ret5) {
    printf("[011] Expect blob export failed. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[011] Blob export success.\n");
}
cubrid_lob_close($lobs);

print("\n\n#####negative example: lob_size#####\n");
$fp1 = fopen('php/_09_lob/logo1.png', 'rb');
$size1=cubrid_lob_size($fp1);
if($size1 == FALSE){
   printf("[011] Expect lob size false.[%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
   printf("[011] Lob size success.\n");
}
fclose($fp1);

$size2=cubrid_lob_size("php/_09_lob/logo1.png");
if($size2 == FALSE){
   printf("[012] Expect lob size false.[%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
   printf("[012] Lob size success.\n");
}

$size3=cubrid_lob_size("nothisfile");
if($size3 == FALSE){
   printf("[013] Expect lob size false.[%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
   printf("[013] Lob size success.\n");
}
$size4=cubrid_lob_size();
if($size4 == FALSE){
   printf("[014] Expect lob size false.[%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
   printf("[014] Lob size success.\n");
}

cubrid_commit($conn);
cubrid_disconnect($conn);

print "Finished!\n";

//rm export file
unlink("php/_09_lob/e1.jpg");
unlink("php/_09_lob/e2.jpg");
unlink("php/_09_lob/e3.jpg");
?>
--CLEAN--
--EXPECTF--
#####positive example: lob_get lob_export lob_size#####
Blobo data export successful
Blob1 data export successful
Blob2 data export successful


#####negative example: lob_get#####

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


#####negative example: lob_export#####

Notice: Undefined offset: -1 in %s on line %d

Warning: cubrid_lob_export() expects parameter 2 to be resource, null given in %s on line %d
[006] Expect blob export failed. [0] []

Notice: Undefined offset: 3 in %s on line %d

Warning: cubrid_lob_export() expects parameter 2 to be resource, null given in %s on line %d
[007] Expect blob export failed. [0] []

Warning: cubrid_lob_export(): Filename cannot be empty in %s on line %d
[008] Expect blob export failed. [0] []

Warning: cubrid_lob_export() expects parameter 2 to be resource, array given in %s on line %d
[009] Expect blob export failed. [0] []

Warning: cubrid_lob_export() expects exactly 3 parameters, 2 given in %s on line %d
[010] Expect blob export failed. [0] []

Warning: cubrid_lob_export() expects exactly 3 parameters, 1 given in %s on line %d
[011] Expect blob export failed. [0] []


#####negative example: lob_size#####

Warning: cubrid_lob_size(): supplied resource is not a valid CUBRID Lob resource in %s on line %d
[011] Expect lob size false.[0] []

Warning: cubrid_lob_size() expects parameter 1 to be resource, string given in %s on line %d
[012] Expect lob size false.[0] []

Warning: cubrid_lob_size() expects parameter 1 to be resource, string given in %s on line %d
[013] Expect lob size false.[0] []

Warning: cubrid_lob_size() expects exactly 1 parameter, 0 given in %s on line %d
[014] Expect lob size false.[0] []
Finished!

