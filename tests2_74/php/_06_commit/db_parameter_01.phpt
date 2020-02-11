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
$db_params1 = cubrid_get_db_parameter($Xconn);
if (FALSE == $db_params1) {
    printf("[001]Expect [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    var_dump($db_params1);
}

$db_params2 = cubrid_get_db_parameter();
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

Notice: Undefined variable: Xconn in %s on line %d

Warning: cubrid_get_db_parameter() expects parameter 1 to be resource, null given in %s on line %d
[001]Expect [0] []

Warning: cubrid_get_db_parameter() expects exactly 1 parameter, 0 given in %s on line %d
[002]Expect [0] []
Finished!
