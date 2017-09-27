--TEST--
cubrid_set_db_parameter
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
printf("#####positive example#####\n");
$db_params= cubrid_get_db_parameter($conn);
var_dump($db_params);
printf("\n");

cubrid_set_db_parameter($conn, CUBRID_PARAM_ISOLATION_LEVEL, 5);
$db_params1 = cubrid_get_db_parameter($conn);
while(list($param_name,$param_value)=each($db_params1)){
   printf("%-30s,%s\n",$param_name,$param_value);
}
printf("\n");

cubrid_set_db_parameter($conn, CUBRID_PARAM_ISOLATION_LEVEL, 4);
$db_params2 = cubrid_get_db_parameter($conn);
while(list($param_name,$param_value)=each($db_params2)){
   printf("%-30s,%s\n",$param_name,$param_value);
}
printf("\n");

cubrid_set_db_parameter($conn, CUBRID_PARAM_LOCK_TIMEOUT, 1);
cubrid_set_autocommit($conn, CUBRID_AUTOCOMMIT_FALSE);
$db_params3 = cubrid_get_db_parameter($conn);
while(list($param_name,$param_value)=each($db_params3)){
   printf("%-30s,%s\n",$param_name,$param_value);
}
printf("\n");


printf("\n\n#####negative example#####\n");

//for CUBRID_PARAM_ISOLATION_LEVEL
$params1=cubrid_set_db_parameter($conn, CUBRID_PARAM_ISOLATION_LEVEL, 7);
if (FALSE == $params1) {
    printf("[001]Expect [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    var_dump(cubrid_get_db_parameter($conn));
}

$params2=cubrid_set_db_parameter($conn, CUBRID_PARAM_ISOLATION_LEVEL, 0);
if (FALSE == $params2) {
    printf("[002]Expect [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    var_dump(cubrid_get_db_parameter($conn));
}

//for PARAM_LOCK_TIMEOUT
$params3=cubrid_set_db_parameter($conn, CUBRID_PARAM_LOCK_TIMEOUT,-2);
if (FALSE == $params3) {
    printf("[003]Expect [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    var_dump(cubrid_get_db_parameter($conn));
}

//for PARAM_AUTO_COMMIT
$params4=cubrid_set_db_parameter($conn,PARAM_AUTO_COMMIT,CUBRID_AUTOCOMMIT_FALSE);
if (FALSE == $params4) {
    printf("[004]Expect [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    var_dump(cubrid_get_db_parameter($conn));
}

//2 parameters
$params5=cubrid_set_db_parameter($conn,CUBRID_PARAM_LOCK_TIMEOUT);
if (FALSE == $params5) {
    printf("[005]Expect [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    var_dump(cubrid_get_db_parameter($conn));
}

$params6=cubrid_set_db_parameter($conn);
if (FALSE == $params6) {
    printf("[006]Expect [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    var_dump(cubrid_get_db_parameter($conn));
}

$params7=cubrid_set_db_parameter();
if (FALSE == $params7) {
    printf("[007]Expect [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    var_dump(cubrid_get_db_parameter($conn));
}


printf("\n\n");
cubrid_set_db_parameter($conn, CUBRID_PARAM_ISOLATION_LEVEL,4);
cubrid_set_db_parameter($conn, CUBRID_PARAM_LOCK_TIMEOUT,-1 );
cubrid_set_autocommit($conn, CUBRID_AUTOCOMMIT_TRUE);
$params_new = cubrid_get_db_parameter($conn);
var_dump($params_new);
printf("\n\n");

cubrid_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
array(4) {
  ["PARAM_ISOLATION_LEVEL"]=>
  int(4)
  ["PARAM_LOCK_TIMEOUT"]=>
  int(-1)
  ["PARAM_MAX_STRING_LENGTH"]=>
  int(1073741823)
  ["PARAM_AUTO_COMMIT"]=>
  int(1)
}

PARAM_ISOLATION_LEVEL         ,5
PARAM_LOCK_TIMEOUT            ,-1
PARAM_MAX_STRING_LENGTH       ,1073741823
PARAM_AUTO_COMMIT             ,1

PARAM_ISOLATION_LEVEL         ,4
PARAM_LOCK_TIMEOUT            ,-1
PARAM_MAX_STRING_LENGTH       ,1073741823
PARAM_AUTO_COMMIT             ,1

PARAM_ISOLATION_LEVEL         ,4
PARAM_LOCK_TIMEOUT            ,1
PARAM_MAX_STRING_LENGTH       ,1073741823
PARAM_AUTO_COMMIT             ,0



#####negative example#####

Warning: Error: DBMS, -1157, Isolation level value in MVCC must be 'read committed', 'repeatable read' or 'serializable'.%s in %s on line %d
[001]Expect [-1157] [Isolation level value in MVCC must be 'read committed', 'repeatable read' or 'serializable'.%s]

Warning: Error: DBMS, -1157, Isolation level value in MVCC must be 'read committed', 'repeatable read' or 'serializable'.%s in %s on line %d
[002]Expect [-1157] [Isolation level value in MVCC must be 'read committed', 'repeatable read' or 'serializable'.%s]
array(4) {
  ["PARAM_ISOLATION_LEVEL"]=>
  int(4)
  ["PARAM_LOCK_TIMEOUT"]=>
  int(-1)
  ["PARAM_MAX_STRING_LENGTH"]=>
  int(1073741823)
  ["PARAM_AUTO_COMMIT"]=>
  int(0)
}

Notice: Use of undefined constant PARAM_AUTO_COMMIT - assumed 'PARAM_AUTO_COMMIT' in %s on line %d

Warning: cubrid_set_db_parameter() expects parameter 2 to be integer, string given in %s on line %d
[004]Expect [0] []

Warning: cubrid_set_db_parameter() expects exactly 3 parameters, 2 given in %s on line %d
[005]Expect [0] []

Warning: cubrid_set_db_parameter() expects exactly 3 parameters, 1 given in %s on line %d
[006]Expect [0] []

Warning: cubrid_set_db_parameter() expects exactly 3 parameters, 0 given in %s on line %d
[007]Expect [0] []


array(4) {
  ["PARAM_ISOLATION_LEVEL"]=>
  int(4)
  ["PARAM_LOCK_TIMEOUT"]=>
  int(-1)
  ["PARAM_MAX_STRING_LENGTH"]=>
  int(1073741823)
  ["PARAM_AUTO_COMMIT"]=>
  int(1)
}


Finished!
