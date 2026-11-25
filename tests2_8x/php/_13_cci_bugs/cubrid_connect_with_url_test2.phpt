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
$conn5=cubrid_connect_with_url("CUBRID:$host:$port:$db:::?autocommit=off",$user, $passwd);
try {
    $autocommit=cubrid_get_autocommit($conn5);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $autocommit = NULL;
}
printf("[005]autocommit value: %s\n",$autocommit);
try {
    $db_params = cubrid_get_db_parameter($conn5);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $db_params = NULL;
}
foreach ($db_params as $param_name => $param_value)  {
    printf("%-30s %s\n", $param_name, $param_value);
}

printf("\n\n");
$conn6=cubrid_connect_with_url("CUBRID:$host:$port:$db:::",$user, $passwd);
$autocommit=cubrid_get_autocommit($conn6);
printf("[006]autocommit value: %s\n",$autocommit);
$db_params = cubrid_get_db_parameter($conn6);
foreach ($db_params as $param_name => $param_value)  {
    printf("%-30s %s\n", $param_name, $param_value);
}

cubrid_close($conn6);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
Warning: Error: CCI, -20030, Invalid url string in %s on line %d
cubrid_get_autocommit(): Argument #1 ($conn_id) must be of type resource, %s given
[005]autocommit value: 
cubrid_get_db_parameter(): Argument #1 ($conn_id) must be of type resource, %s given

Warning: foreach() argument must be of type array|object, %s given in %s on line %d


[006]autocommit value: 1
PARAM_ISOLATION_LEVEL          4
PARAM_LOCK_TIMEOUT             -1
PARAM_MAX_STRING_LENGTH        1073741823
PARAM_AUTO_COMMIT              1
Finished!
