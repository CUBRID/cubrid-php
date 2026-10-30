--TEST--
cubrid_get_autocommit cubrid_set_autocommit
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

printf("#####correct example#####\n");
if (cubrid_get_autocommit($conn)) {
    printf("[001]Expect: autocommit is ON.\n");
} else {
    printf("[001]No expect: autocommit is OFF.");
}

cubrid_set_autocommit($conn,CUBRID_AUTOCOMMIT_FALSE);
if (cubrid_get_autocommit($conn)) {
    printf("[002]No expect: autocommit is ON.\n");
} else {
    printf("[002]Expect: autocommit is OFF.\n");
}
cubrid_commit($conn);
cubrid_close($conn);

$conn = cubrid_connect($host, $port, $db, $user, $passwd);
if (cubrid_get_autocommit($conn)) {
    printf("[003]Expect: autocommit is ON.\n");
} else {
    printf("[003]No Expect: autocommit is OFF.\n");
}

cubrid_set_autocommit($conn,CUBRID_AUTOCOMMIT_TRUE);
cubrid_close($conn);

$conn = cubrid_connect($host, $port, $db, $user, $passwd);
if (cubrid_get_autocommit($conn)) {
    printf("[004]Expect: autocommit is ON.\n");
} else {
    printf("[004]No expect: autocommit is OFF.");
}


printf("\n\n#####negative example#####\n");
try {
    $get5=cubrid_get_autocommit($conn,'');
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $get5 = NULL;
}
if(FALSE == $get5){
   printf("[005]Expect false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

try {
    $get6=cubrid_get_autocommit(null);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $get6 = NULL;
}
if(FALSE == $get6){
   printf("[006]Expect false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

try {
    $get7=cubrid_get_autocommit();
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $get7 = NULL;
}
if(FALSE == $get7){
   printf("[007]Expect false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

try {
    $set8=cubrid_set_autocommit($conn);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $set8 = NULL;
}
if(FALSE == $set8){
   printf("[008]Expect false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

try {
    $set9=cubrid_set_autocommit();
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $set9 = NULL;
}
if(FALSE == $set9){
   printf("[009]Expect false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}


cubrid_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####correct example#####
[001]Expect: autocommit is ON.
[002]Expect: autocommit is OFF.
[003]Expect: autocommit is ON.
[004]Expect: autocommit is ON.


#####negative example#####
cubrid_get_autocommit() expects exactly 1 argument, 2 given
[005]Expect false, [0] []
cubrid_get_autocommit(): Argument #1 ($conn_id) must be of type resource, null given
[006]Expect false, [0] []
cubrid_get_autocommit() expects exactly 1 argument, 0 given
[007]Expect false, [0] []
cubrid_set_autocommit() expects exactly 2 arguments, 1 given
[008]Expect false, [0] []
cubrid_set_autocommit() expects exactly 2 arguments, 0 given
[009]Expect false, [0] []
Finished!
