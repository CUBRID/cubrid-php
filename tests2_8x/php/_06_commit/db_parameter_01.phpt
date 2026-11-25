--TEST--
cubrid_db_parameter 
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
//cubrid_get_db_parameter
$db_params = cubrid_get_db_parameter($conn);
foreach ($db_params as $param_name => $param_value) {
   printf("%-30s,%s\n",$param_name,$param_value);
}
printf("\n");

printf("\n\n#####negative example#####\n");
try {
    $db_params1 = cubrid_get_db_parameter($Xconn);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $db_params1 = NULL;
}
if (FALSE == $db_params1) {
    printf("[001]Expect [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    var_dump($db_params1);
}

try {
    $db_params2 = cubrid_get_db_parameter();
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $db_params2 = NULL;
}
if (FALSE == $db_params2) {
    printf("[002]Expect [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    var_dump($db_params2);
}


cubrid_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
PARAM_ISOLATION_LEVEL         ,4
PARAM_LOCK_TIMEOUT            ,-1
PARAM_MAX_STRING_LENGTH       ,1073741823
PARAM_AUTO_COMMIT             ,1



#####negative example#####

Warning: Undefined variable $Xconn in %s on line %d
cubrid_get_db_parameter(): Argument #1 ($conn_id) must be of type resource, null given
[001]Expect [0] []
cubrid_get_db_parameter() expects exactly 1 argument, 0 given
[002]Expect [0] []
Finished!
