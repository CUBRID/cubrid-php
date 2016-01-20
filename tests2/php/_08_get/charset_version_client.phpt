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

//charset and encoding
$charset = cubrid_get_charset($conn);
var_dump($charset);

$client_encoding=cubrid_client_encoding($conn);
var_dump($client_encoding);

if($charset == $client_encoding ){
   printf("cubrid_get_charset equal cubrid_client_encoding\n");
}else{
   printf("cubrid_get_charset is not equal cubrid_client_encoding\n");
}

//server info and client info
$cubrid_php_version= cubrid_version();
printf("CUBRID PHP module's version: %s\n",$cubrid_php_version);

$cubrid_server_version=cubrid_get_server_info($conn);
printf("CUBRID server version: %s\n",$cubrid_server_version);

$cubrid_library_version=cubrid_get_client_info();
printf("client library version: %s\n",$cubrid_library_version);

cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
string(9) "iso8859-1"
string(9) "iso8859-1"
cubrid_get_charset equal cubrid_client_encoding
CUBRID PHP module's version: 9.1.0.0001
CUBRID server version: %s
client library version: 9.1.0
Finished!
