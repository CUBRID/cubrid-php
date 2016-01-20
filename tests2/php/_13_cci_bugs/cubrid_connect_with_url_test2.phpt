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
$conn5=cubrid_connect_with_url("CUBRID:$host:$port:$db:::?autocommit=off",$user, $passwd);
$autocommit=cubrid_get_autocommit($conn5);
printf("[005]autocommit value: %s\n",$autocommit);
$db_params = cubrid_get_db_parameter($conn5);
while (list($param_name, $param_value) = each($db_params)) {
    printf("%-30s %s\n", $param_name, $param_value);
}

printf("\n\n");
$conn6=cubrid_connect_with_url("CUBRID:$host:$port:$db:::?autocommit=on",$user, $passwd);
$autocommit=cubrid_get_autocommit($conn6);
printf("[006]autocommit value: %s\n",$autocommit);
$db_params = cubrid_get_db_parameter($conn6);
while (list($param_name, $param_value) = each($db_params)) {
    printf("%-30s %s\n", $param_name, $param_value);
}

cubrid_close($conn5);
cubrid_close($conn6);

print "Finished!\n";
?>
--CLEAN--
--XFAIL--
"autocommit" setting in url is closed in CCI 9.0.0
--EXPECTF--
[005]autocommit value: 1
PARAM_ISOLATION_LEVEL          3
PARAM_LOCK_TIMEOUT             -1
PARAM_MAX_STRING_LENGTH        1073741823
PARAM_AUTO_COMMIT              0


[006]autocommit value: 1
PARAM_ISOLATION_LEVEL          3
PARAM_LOCK_TIMEOUT             -1
PARAM_MAX_STRING_LENGTH        1073741823
PARAM_AUTO_COMMIT              1
Finished!
