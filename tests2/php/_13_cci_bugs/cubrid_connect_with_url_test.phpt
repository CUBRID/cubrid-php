--TEST--
cubrid_bind
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn1=cubrid_connect_with_url("CUBRID:$host:$port:$db:$user:$passwd:::?autocommit=on","aaaa");
if (FALSE == $conn1) {
    printf("[001]Expect: return value false. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn1){
    printf("[001]No Expect: return value true\n");
    cubrid_close($conn1);
}else{
    printf("[001]no true and no false");
}

$conn2=cubrid_connect_with_url("CUBRID:$host:$port:$db:$user:$passwd:::?autocommit=on","dba","123456");
if (FALSE == $conn2) {
    printf("[002]Expect: return value false. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn2){
    printf("[002]No Expect: return value true\n");
    cubrid_close($conn2);
}else{
    printf("[002]no true and no false");
}

$conn3=cubrid_connect_with_url("CUBRID:$host:$port:$db:$user:$passwd:::?autocommit=on");
if (FALSE == $conn3) {
    printf("[003]No Expect: return value false. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn3){
    printf("[003]Expect: return value true\n");
    cubrid_close($conn3);
}else{
    printf("[003]no true and no false");
}

cubrid_close_prepare($result);

cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--XFAIL--
"autocommit" setting in url is closed in CCI 8.4.4
--EXPECTF--
PHP Warning:  Error: DBMS, 0, Unknown DBMS error in %s on line %d
[001]Expect: return value false. [0] [Unknown DBMS error]
PHP Warning:  Error: DBMS, 0, Unknown DBMS error in %s on line %d
[002]Expect: return value false. [0] [Unknown DBMS error]
[003]Expect: return value true
