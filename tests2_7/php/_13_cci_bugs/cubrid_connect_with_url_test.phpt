--TEST--
cubrid_connect_with_url_
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn1=cubrid_connect_with_url("CUBRID:$host:$port:$db:$user:$passwd:?autocommit=on","aaaa");
if (FALSE == $conn1) {
    printf("[001]Expect: return value false. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn1){
    printf("[001]No Expect: return value true\n");
    cubrid_close($conn1);
}else{
    printf("[001]no true and no false");
}

$conn2=cubrid_connect_with_url("CUBRID:$host:$port:$db:$user:$passwd:?autocommit=on","dba","123456");
if (FALSE == $conn2) {
    printf("[002]Expect: return value false. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn2){
    printf("[002]No Expect: return value true\n");
    cubrid_close($conn2);
}else{
    printf("[002]no true and no false");
}

$conn3=cubrid_connect_with_url("CUBRID:$host:$port:$db:$user:$passwd:?autocommit=on");
if (FALSE == $conn3) {
    printf("[003]No Expect: return value false. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn3){
    printf("[003]Expect: return value true\n");
    cubrid_close($conn3);
}else{
    printf("[003]no true and no false");
}

$conn4=cubrid_connect_with_url("CUBRID:$host:$port:$db:::",$user, $passwd);
if (FALSE == $conn4) {
    printf("[004]No Expect: return value false. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn4){
    printf("[004]Expect: return value true\n");
    cubrid_close($conn4);
}else{
    printf("[004]no true and no false");
}

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
Warning: Error: CCI, -20030, Invalid url string in %s on line %d
[001]Expect: return value false. [-20030] [Invalid url string]

Warning: Error: CCI, -20030, Invalid url string in %s on line %d
[002]Expect: return value false. [-20030] [Invalid url string]

Warning: Error: CCI, -20030, Invalid url string in %s on line %d
[003]No Expect: return value false. [-20030] [Invalid url string]
[004]Expect: return value true
Finished!

