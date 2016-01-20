--TEST--
cubrid_version
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php

include_once("connect.inc");

$conn = cubrid_connect_with_url($connect_url);
if (!$conn) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
    exit(1);
}

$host = cubrid_real_escape_string($host);
$port = cubrid_real_escape_string($port,$conn);
$db = cubrid_real_escape_string($user);
$user = cubrid_real_escape_string($user);
$passwd = cubrid_real_escape_string($passwd);

$connect_url_esp = "CUBRID:$host:$port:$db:::";
printf("connect_url_esp: %s\n", $connect_url_esp);
printf("cubrid version: %s\n", cubrid_version());
printf("server version: %s\n", cubrid_get_server_info($conn));
printf("client version: %s\n", cubrid_get_client_info());

//error
$passwd = cubrid_real_escape_string($passwd,-1);
$passwd = cubrid_real_escape_string($passwd,-1,-1);

cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
connect_url_esp: %s
cubrid version: %s
server version: %s
client version: %s

Warning: cubrid_real_escape_string() expects parameter 2 to be resource, %s

Warning: cubrid_real_escape_string() expects at most 2 parameters, 3 given %s
done!
