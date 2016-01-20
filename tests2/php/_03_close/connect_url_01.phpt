--TEST--
cubrid_connect_with_url
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php
include_once("connect.inc");
printf("#####positive example#####\n");

$conn = cubrid_connect_with_url($connect_url, $user, $passwd);
if (!$conn) {
    printf("[001] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}

$conn1 = cubrid_connect_with_url($connect_url, $user, $passwd, FALSE);
$conn2 = cubrid_connect_with_url($connect_url, $user, $passwd, TRUE);
if ($conn != $conn1) {
    printf("[002] The new_link parameter in cubrid_connect_with_url does not work!\n");
}
if ($conn == $conn2) {
    printf("[003] Can not make a new connection with the same parameters!");
}
$conn4 = cubrid_connect_with_url($connect_url);
if (!$conn4) {
    printf("[004] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[004]Connect success\n");
}

$conn5=cubrid_connect_with_url("CUBRID:$host:$port:$db:::",$user, $passwd);
if (FALSE == $conn5) {
    printf("[005]No expect: return value false. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn5){
    printf("[005]Expect: return value true\n");
    $autocommit=cubrid_get_autocommit($conn5);
    printf("[005]autocommit value: %s\n",$autocommit);
}
printf("\n\n");
$conn6=cubrid_connect_with_url("CUBRID:$host:$port:$db:::",$user, $passwd);
if (FALSE == $conn6) {
    printf("[006]No expect: return value false. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn6){
    printf("[006]Expect: return value true\n");
    $autocommit=cubrid_get_autocommit($conn6);
    printf("[006]autocommit value: %s\n",$autocommit);
    $db_params = cubrid_get_db_parameter($conn6);
    while (list($param_name, $param_value) = each($db_params)) {
       printf("%-30s %s\n", $param_name, $param_value);
    }
}

cubrid_close($conn);
cubrid_close($conn1);
cubrid_close($conn2);
cubrid_close($conn4);
cubrid_close($conn5);
cubrid_close($conn6);

$conn7=cubrid_connect_with_url("CUBRID:$host:$port:$db:$user:$passwd:");
if (FALSE == $conn7) {
    printf("[007]No Expect: return value false. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn7){
    printf("[007]Expect: return value true\n");
    $autocommit=cubrid_get_autocommit($conn7);
    printf("[007]autocommit value: %s\n",$autocommit);
}
cubrid_close($conn7);

printf("\n\n#####negative example for disconnect and close#####\n");
$conn8=cubrid_connect_with_url("CUBRID:$host:$port:$db:$user:$passwd:::?autocommit=on","","123456");
if (FALSE == $conn8) {
    printf("[008]Expect: return value false. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn8){
    printf("[008]No Expect: return value true\n");
    $autocommit=cubrid_get_autocommit($conn8);
    printf("[008]autocommit value: %s\n",$autocommit);
}

$conn8=cubrid_connect_with_url("CUBRID:$host:$port:$db:$user:$passwd:::?autocommit=off","","123456");
if (FALSE == $conn8) {
    printf("[009]Expect: return value false. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn8){
    printf("[009]No Expect: return value true\n");
    $autocommit=cubrid_get_autocommit($conn8);
    printf("[009]autocommit value: %s\n",$autocommit);
}

$conn9=cubrid_connect_with_url("CUBRID:$host:$port:$db:$user:???");
if (FALSE == $conn9) {
    printf("[010]Expect: return value false. [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn9){
    printf("[010]No Expect: return value true\n");
    $autocommit=cubrid_get_autocommit($conn9);
    printf("[010]autocommit value: %s\n",$autocommit);
}

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
[004]Connect success
[005]Expect: return value true
[005]autocommit value: 1


[006]Expect: return value true
[006]autocommit value: 1
PARAM_ISOLATION_LEVEL          3
PARAM_LOCK_TIMEOUT             -1
PARAM_MAX_STRING_LENGTH        1073741823
PARAM_AUTO_COMMIT              1
[007]Expect: return value true
[007]autocommit value: 1


#####negative example for disconnect and close#####

Warning: Error: CCI, -16, Cannot connect to CUBRID CAS in %s on line %d
[008]Expect: return value false. [-16] [Cannot connect to CUBRID CAS]

Warning: Error: CCI, -16, Cannot connect to CUBRID CAS in %s on line %d
[009]Expect: return value false. [-16] [Cannot connect to CUBRID CAS]

Warning: Error: CCI, -30, Invalid url string in %s on line %d
[010]Expect: return value false. [-30] [Invalid url string]
Finished!
