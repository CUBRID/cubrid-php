--TEST--
cubrid_lob_send cubrid_lob_close
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
$fp3=fopen('php/_09_lob/clob3.txt','r');
$conn=cubrid_connect($host, $port, $db, $user, $passwd);
cubrid_execute($conn,"drop table if exists send_tb");
cubrid_execute($conn,"CREATE TABLE send_tb(a int AUTO_INCREMENT, f clob)");
$cubrid_req = cubrid_prepare($conn, "insert into send_tb (f) values (?)");
cubrid_bind($cubrid_req, 1, $fp1, "clob");
cubrid_execute($cubrid_req);

$cubrid_req = cubrid_prepare($conn, "insert into send_tb (f) values (?)");
cubrid_bind($cubrid_req, 1, $fp2, "clob");
cubrid_execute($cubrid_req);

$cubrid_req = cubrid_prepare($conn, "insert into send_tb (f) values (?)");
cubrid_bind($cubrid_req, 1, $fp3, "clob");
cubrid_execute($cubrid_req);
fclose($fp1);
fclose($fp2);
fclose($fp3);


print("#####positive example: lob_send lob_close#####\n");
$lobs = cubrid_lob_get($conn, "SELECT f FROM send_tb ");
cubrid_lob_send($conn, $lobs[0]);
print("\n\n\n\n");
cubrid_lob_send($conn, $lobs[1]);
print("\n\n\n\n");
cubrid_lob_send($conn, $lobs[2]);
$close=cubrid_lob_close($lobs);
if($close !== true){
   printf("No expect flase for lob_close, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("Expect true for lob_close\n");
}

print("\n\n#####negative example: lob_send#####\n");
$lobs = cubrid_lob_get($conn, "select f from send_tb ");
try {
    $send1 = cubrid_lob_send($conn, $lobs[-1]);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $send1 = NULL;
}
if (!$send1) {
    printf("[001] Expect lob_send failed. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[001] lob_send success.\n");
}

try {
    $send2 = cubrid_lob_send($conn, $lobs[3]);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $send2 = NULL;
}
if (!$send2) {
    printf("[002] Expect lob_send failed. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[002] lob_send success.\n");
}

try {
    $send3 = cubrid_lob_send($conn, $lobs);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $send3 = NULL;
}
if (!$send2) {
    printf("[003] Expect lob_send failed. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[003] lob_send success.\n");
}

$fp1 = fopen('php/_09_lob/clob1.txt', 'rb');
$send4 = cubrid_lob_send($conn, $fp1);
if (!$send4) {
    printf("[004] Expect lob_send failed. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[004] lob_send success.\n");
}
fclose($fp1);

try {
    $send5 = cubrid_lob_send($conn);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $send5 = NULL;
}
if (!$send5) {
    printf("[005] Expect lob_send failed. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[005] lob_send success.\n");
}


print("\n\n#####negative example: lob_close#####\n");
try {
    $close2 = cubrid_lob_close($lobs[0]);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $close2 = FALSE;
}
if (!$close2) {
    printf("[006] Expect lob_close failed. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[006] lob_send success.\n");
}

try {
    $close3 = cubrid_lob_close();
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $close3 = FALSE;
}
if (!$close3) {
    printf("[007] Expect lob_close failed. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[007] lob_send success.\n");
}

$close4=cubrid_lob_close($lobs);
if($close !== true){
   printf("[008] No Expect lob_close failed, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("[008]Expect true for lob_close\n");
}


cubrid_commit($conn);
cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example: lob_send lob_close#####
create class c_b (b bit(0)); 
create class c_vb (vb bit varying(0));
create class c_c (c char(0)); 
create class c_nc (nc nchar(0));




client 1 is called.
1 : 1 : 1 : 1 : 1 : 20
client 1 is running.
 ** (others: 1) iteration: 1, commit interval: 1, elapse time: 0.141 sec, TPS:
7
Total TPS : 7.0921984
client 1 is called.
1 : 1 : 1 : 1 : 1 : 20
client 1 is running.
 ** (others: 1) iteration: 1, commit interval: 1, elapse time: 0.062 sec, TPS:
16
Total TPS : 16.129032
client 1 is called.
100000 : 1 : 100000 : 1 : 1 : 0
client 1 is running.
 ** (insert: 1) iteration: 100000, commit interval: 1, elapse time: 134.126
sec, TPS: 745
Total TPS : 745.5676
client 1 is called.
100000 : 100001 : 200000 : 1 : 100 : 0
client 1 is running.




** (insert: 3) iteration: 20000, commit interval: 1, elapse time: 49.891 sec,
TPS: 400
 ** (insert: 1) iteration: 20000, commit interval: 1, elapse time: 49.938 sec,
TPS: 400
 ** (insert: 5) iteration: 20000, commit interval: 1, elapse time: 50.016 sec,
TPS: 399
 ** (insert: 4) iteration: 20000, commit interval: 1, elapse time: 50.125 sec,
TPS: 399
 ** (insert: 2) iteration: 20000, commit interval: 1, elapse time: 50.313 sec,
TPS: 397
 ** (insert: 1) iteration: 20000, commit interval: 100, elapse time: 16.25
sec, TPS: 1230
 ** (insert: 5) iteration: 20000, commit interval: 100, elapse time: 16.25
sec, TPS: 1230
 ** (insert: 4) iteration: 20000, commit interval: 100, elapse time: 16.25
sec, TPS: 1230
 ** (insert: 2) iteration: 20000, commit interval: 100, elapse time: 16.25
sec, TPS: 1230
 ** (insert: 3) iteration: 20000, commit interval: 100, elapse time: 16.25
sec, TPS: 1230
Expect true for lob_close


#####negative example: lob_send#####

Warning: Undefined array key -1 in %s on line %d
cubrid_lob_send(): Argument #2 ($lob_id) must be of type resource, null given
[001] Expect lob_send failed. [0] []

Warning: Undefined array key 3 in %s on line %d
cubrid_lob_send(): Argument #2 ($lob_id) must be of type resource, null given
[002] Expect lob_send failed. [0] []
cubrid_lob_send(): Argument #2 ($lob_id) must be of type resource, array given
[003] Expect lob_send failed. [0] []

Warning: cubrid_lob_send(): supplied resource is not a valid CUBRID Lob resource in %s on line %d
[004] Expect lob_send failed. [0] []
cubrid_lob_send() expects exactly 2 arguments, 1 given
[005] Expect lob_send failed. [0] []


#####negative example: lob_close#####
cubrid_lob_close(): Argument #1 ($lob_id_array) must be of type array, resource given
[006] Expect lob_close failed. [0] []
cubrid_lob_close() expects exactly 1 argument, 0 given
[007] Expect lob_close failed. [0] []
[008]Expect true for lob_close
Finished!
