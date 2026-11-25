--TEST--
cubrid_batch_execute: too many arguments are rejected and nothing is executed
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
cubrid_execute($conn, "drop table if exists bug_batch_tb");
cubrid_execute($conn, "create table bug_batch_tb (id int)");

try {
    $res = cubrid_batch_execute($conn, array("insert into bug_batch_tb values (1)"), "extra");
} catch (ArgumentCountError $e) {
    echo $e->getMessage(), "\n";
    $res = NULL;
}
var_dump($res);

$req = cubrid_execute($conn, "select count(*) from bug_batch_tb");
$row = cubrid_fetch_row($req);
printf("rows: %s\n", $row[0]);

cubrid_execute($conn, "drop table if exists bug_batch_tb");
cubrid_disconnect($conn);
print "done!\n";
?>
--CLEAN--
--EXPECTF--
cubrid_batch_execute() expects exactly 2 arguments, 3 given
NULL
rows: 0
done!
