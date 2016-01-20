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
$charset2 = cubrid_get_charset(NULL);
if(FALSE == $charset2){
   printf("[002]Expect: return false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   var_dump($charset);
}

$client_encoding3=cubrid_client_encoding();
var_dump($client_encoding3);
$client_encoding3=cubrid_client_encoding(NULL);
if(FALSE ==$client_encoding3){
   printf("[003]Expect: return false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   var_dump($client_encoding3);
}

//server info and client info
$cubrid_php_version4= cubrid_version($conn);
if(FALSE ==$cubrid_php_version4){
   printf("[004]Expect: return false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("CUBRID PHP module's version: %s\n",$cubrid_php_version);
}

$cubrid_server_version=cubrid_get_server_info();
if(FALSE ==$cubrid_server_version){
   printf("[005]Expect: return false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("CUBRID server version: %s\n",$cubrid_server_version);
}

$cubrid_library_version=cubrid_get_client_info($conn);
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

Warning: cubrid_get_charset() expects parameter 1 to be resource, null given in %s on line %d
[002]Expect: return false [0] []
string(9) "iso8859-1"

Warning: cubrid_client_encoding() expects parameter 1 to be resource, null given in %s on line %d
[003]Expect: return false [0] []

Warning: cubrid_version() expects exactly 0 parameters, 1 given in %s on line %d
[004]Expect: return false [0] []

Warning: cubrid_get_server_info() expects exactly 1 parameter, 0 given in %s on line %d
[005]Expect: return false [0] []

Warning: cubrid_get_client_info() expects exactly 0 parameters, 1 given in %s on line %d
[006]Expect: return false [0] []
Finished!
