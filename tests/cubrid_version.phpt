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

printf("Current PHP version: %s\n", phpversion());
//phpinfo();

$conn = cubrid_connect($host, $port, $db, $user, $passwd);

if (!$conn) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
    exit(1);
}

printf("cubrid version: %s\n", cubrid_version());
printf("server version: %s\n", cubrid_get_server_info($conn));
printf("client version: %s\n", cubrid_get_client_info());

$db_list = cubrid_list_dbs($conn);
$i = 0;
$cnt = count($db_list);
while ($i < $cnt) {
    $db_name = cubrid_db_name($db_list, $i);
    if ($db_name == "demodb") {
        printf("database name: %s\n", $db_name);
    }
    $i++;
}

cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
%s
cubrid version: %s
server version: %s
client version: %s
database name: demodb
done!
