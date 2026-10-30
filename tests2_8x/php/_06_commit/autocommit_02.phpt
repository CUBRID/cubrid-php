--TEST--
cubrid_commit cubrid_rollback
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
if (cubrid_get_autocommit($conn)) {
    printf("Autocommit is ON.\n");
} else {
    printf("Autocommit is OFF.");
}

cubrid_set_autocommit($conn,CUBRID_AUTOCOMMIT_FALSE);
cubrid_execute($conn, "DROP TABLE if exists commit1_tb");
cubrid_query('CREATE TABLE commit1_tb(a int, b varchar(10))');
cubrid_query('INSERT INTO commit1_tb(a) VALUE(1),(2),(3)');
cubrid_commit($conn);

printf("#####negative example for cubrid_commit()#####\n");
cubrid_query('INSERT INTO commit1_tb(a) VALUE(4)');
try {
    $conn_res=cubrid_commit($conn,'');
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $conn_res = NULL;
}
if(FALSE == $conn_res){
   printf("[001]Expect false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

try {
    $conn_res2=cubrid_commit('');
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $conn_res2 = NULL;
}
if(FALSE == $conn_res2){
   printf("[002]Expect false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

try {
    $conn_res3=cubrid_commit();
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $conn_res3 = NULL;
}
if(FALSE == $conn_res3){
   printf("[003]Expect false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}
cubrid_close($conn);

$conn = cubrid_connect($host, $port, $db, $user, $passwd);
$req4 = cubrid_query('SELECT * FROM commit1_tb where a=4');
if(FALSE == $req4){
   printf("[004]Expect false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   $result = cubrid_fetch_assoc($req4);
   printf("[004]\n");
   var_dump($result);
}


cubrid_set_autocommit($conn,CUBRID_AUTOCOMMIT_FALSE);
if (cubrid_get_autocommit($conn)) {
    printf("No expect :autocommit is ON.\n");
} else {
    printf("Expect: autocommit is OFF.");
}

printf("\n\n#####negative example for cubrid_rollback()#####\n");
cubrid_query("delete from commit1_tb where a=3 ");
try {
    $roll_res5=cubrid_rollback($conn,'');
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $roll_res5 = NULL;
}
if(FALSE == $roll_res5){
   printf("[005]Expect false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

try {
    $roll_res6=cubrid_rollback('');
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $roll_res6 = NULL;
}
if(FALSE == $roll_res6){
   printf("[006]Expect false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

try {
    $roll_res7=cubrid_rollback();
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $roll_res7 = NULL;
}
if(FALSE == $roll_res7){
   printf("[007]Expect false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

cubrid_commit($conn);
$req = cubrid_query('SELECT * FROM commit1_tb where a=3');
$result = cubrid_fetch_assoc($req);
printf("Rollback failed result:\n");
var_dump($result);

cubrid_set_autocommit($conn,CUBRID_AUTOCOMMIT_TRUE);

cubrid_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
Autocommit is ON.
#####negative example for cubrid_commit()#####
cubrid_commit() expects exactly 1 argument, 2 given
[001]Expect false, [0] []
cubrid_commit(): Argument #1 ($conn_id) must be of type resource, string given
[002]Expect false, [0] []
cubrid_commit() expects exactly 1 argument, 0 given
[003]Expect false, [0] []
[004]
bool(false)
Expect: autocommit is OFF.

#####negative example for cubrid_rollback()#####
cubrid_rollback() expects exactly 1 argument, 2 given
[005]Expect false, [0] []
cubrid_rollback(): Argument #1 ($conn_id) must be of type resource, string given
[006]Expect false, [0] []
cubrid_rollback() expects exactly 1 argument, 0 given
[007]Expect false, [0] []
Rollback failed result:
bool(false)
Finished!
