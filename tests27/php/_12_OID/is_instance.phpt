--TEST--
cubrid_is_instance
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php

include_once('connect.inc');

$conn = cubrid_connect($host, $port, $db, $user, $passwd);
cubrid_execute($conn,"drop table if exists code;");
cubrid_execute($conn,"create table code(last_name varchar(10), first_name varchar(20))");
cubrid_execute($conn,"insert into code values('X','Mixed'),('W','Woman'),('M','Man'),('B','Bronze')");
if (!($req = cubrid_execute($conn, 'SELECT * FROM code', CUBRID_INCLUDE_OID))) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

$oid = cubrid_current_oid($req);
if (is_null ($oid)){
    printf("[002] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

$res = cubrid_is_instance($conn, $oid);
if ($res == 1) {
    printf("Intance pointed by %s exists.\n", $oid);
} else {
    printf ("[003] [%d] %s\n", cburid_errno($conn), cubrid_error($conn));
}

cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
Intance pointed by %s exists.
done!
