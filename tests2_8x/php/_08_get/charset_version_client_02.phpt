--TEST--
charset_version_client
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php
include_once("connect.inc");

$conn = cubrid_connect_with_url($connect_url, $user, $passwd);
if (!$conn) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
    exit(1);
}
printf("#####negative example#####\n");
//charset and encoding
try {
    $charset2 = cubrid_get_charset(NULL);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $charset2 = NULL;
}
if(FALSE == $charset2){
   printf("[002]Expect: return false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   var_dump($charset);
}

$client_encoding3=cubrid_client_encoding();
var_dump($client_encoding3);
try {
    $client_encoding3=cubrid_client_encoding(NULL);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $client_encoding3 = NULL;
}
if(FALSE ==$client_encoding3){
   printf("[003]Expect: return false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   var_dump($client_encoding3);
}

//server info and client info
try {
    $cubrid_php_version4= cubrid_version($conn);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $cubrid_php_version4 = NULL;
}
if(FALSE ==$cubrid_php_version4){
   printf("[004]Expect: return false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("CUBRID PHP module's version: %s\n",$cubrid_php_version);
}

try {
    $cubrid_server_version=cubrid_get_server_info();
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $cubrid_server_version = NULL;
}
if(FALSE ==$cubrid_server_version){
   printf("[005]Expect: return false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("CUBRID server version: %s\n",$cubrid_server_version);
}

try {
    $cubrid_library_version=cubrid_get_client_info($conn);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $cubrid_library_version = NULL;
}
if(FALSE ==$cubrid_library_version){
   printf("[006]Expect: return false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    printf("client library version: %s\n",$cubrid_library_version);
}

cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####negative example#####
cubrid_get_charset(): Argument #1 ($conn_id) must be of type resource, null given
[002]Expect: return false [0] []
string(9) "iso8859-1"
cubrid_client_encoding(): Argument #1 ($conn_id) must be of type resource, null given
[003]Expect: return false [0] []
cubrid_version() expects exactly 0 arguments, 1 given
[004]Expect: return false [0] []
cubrid_get_server_info() expects exactly 1 argument, 0 given
[005]Expect: return false [0] []
cubrid_get_client_info() expects exactly 0 arguments, 1 given
[006]Expect: return false [0] []
Finished!
