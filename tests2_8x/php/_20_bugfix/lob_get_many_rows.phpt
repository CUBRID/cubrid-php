--TEST--
cubrid_lob_get: result array stays valid with more than 8 rows (array header was overwritten)
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
cubrid_execute($conn, "drop table if exists bug_lob_get_tb");
cubrid_execute($conn, "create table bug_lob_get_tb (id int, c clob)");
for ($i = 0; $i < 20; $i++) {
    cubrid_execute($conn, "insert into bug_lob_get_tb values ($i, CHAR_TO_CLOB('" . str_repeat('x', $i + 1) . "'))");
}

$lobs = cubrid_lob_get($conn, "select c from bug_lob_get_tb order by id");
printf("count: %d\n", count($lobs));
$sizes = array();
foreach ($lobs as $lob) {
    $sizes[] = cubrid_lob_size($lob);
}
echo implode(",", $sizes), "\n";

$lobs[] = "appended";
$lobs["key"] = "string key";
printf("count after append: %d\n", count($lobs));
unset($lobs);

cubrid_execute($conn, "drop table if exists bug_lob_get_tb");
cubrid_disconnect($conn);
print "done!\n";
?>
--CLEAN--
--EXPECTF--
count: 20
1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20
count after append: 22
done!
