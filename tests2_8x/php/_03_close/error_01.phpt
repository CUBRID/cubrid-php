--TEST--
cubrid_error_msg cubrid_error_code_facility cubrid_error cubrid_errno
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php
include_once('connect.inc');
printf("\n\n#####negative example#####\n");
$conn1 = cubrid_connect($host, $port, $db, $user, "124456");
if (FALSE == $conn1) {
    try {
        printf("[001]Expect: return value false, [%d] [%s]\n", cubrid_error_code($conn), cubrid_error_msg());
    } catch (ArgumentCountError | TypeError $e) {
        echo $e->getMessage(), "\n";
    }
}elseif(TRUE == $conn1){
    printf("[001]No Expect: return value true, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[001]no true and no false\n");
}

$conn2 = cubrid_connect($host, $port, $db,'dbaa', $passwd);
if (FALSE == $conn2) {
    try {
        printf("[002]Expect: return value false, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg($conn));
    } catch (ArgumentCountError | TypeError $e) {
        echo $e->getMessage(), "\n";
    }
}elseif(TRUE == $conn2){
    printf("[002]No Expect: return value true, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[002]no true and no false\n");
}

$conn3 = cubrid_connect($host, $port, "nothisdb");
if (FALSE == $conn3) {
    try {
        printf("[003]Expect: return value false, [%d] [%s]\n", cubrid_errno(NULL), cubrid_error_msg());
    } catch (ArgumentCountError | TypeError $e) {
        echo $e->getMessage(), "\n";
    }
}elseif(TRUE == $conn3){
    printf("[003]No Expect: return value true, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[003]no true and no false\n");
}

$conn4 = cubrid_connect($host, $port, "demodbb");
if (FALSE == $conn4) {
    try {
        printf("[004]No Expect: return value false, [%d] [%s]\n", cubrid_error_code(), cubrid_error(NULL));
    } catch (ArgumentCountError | TypeError $e) {
        echo $e->getMessage(), "\n";
    }
}elseif(TRUE == $conn4){
    printf("[004]Expect: return value true, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[004]no true and no false\n");
}

$conn5 = cubrid_connect($host, $port,"phpdbd");
printf("conn5: %d\n",$conn5);
if (FALSE == $conn5) {
    try {
        printf("[005]Expect: return value false, [%d] [%s]\n", cubrid_error_code_facility('1'), cubrid_error_msg());
    } catch (ArgumentCountError | TypeError $e) {
        echo $e->getMessage(), "\n";
    }
}elseif(TRUE == $conn5){
    printf("[005]No Expect: return value true, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[005]no true and no false\n");
}

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####negative example#####

Warning: Error: DBMS, -171, Incorrect or missing password.%s in %s on line %d

Warning: Undefined variable $conn in %s on line %d
cubrid_error_code() expects exactly 0 arguments, 1 given

Warning: Error: DBMS, -165, User "dbaa" is invalid.%s in %s on line %d

Warning: Undefined variable $conn in %s on line %d
cubrid_error_msg() expects exactly 0 arguments, 1 given

Warning: Error: DBMS, -677, Failed to connect to database server, 'nothisdb', on the following host(s): localhost:localhost%s in %s on line %d
cubrid_errno(): Argument #1 ($conn) must be of type resource, null given

Warning: Error: DBMS, -677, Failed to connect to database server, 'demodbb', on the following host(s): localhost:localhost%s in %s on line %d
cubrid_error(): Argument #1 ($conn) must be of type resource, null given

Warning: Error: DBMS, -677, Failed to connect to database server, 'phpdbd', on the following host(s): localhost:localhost%s in %s on line %d
conn5: 0
cubrid_error_code_facility() expects exactly 0 arguments, 1 given
Finished!
