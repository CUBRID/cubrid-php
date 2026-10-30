--TEST--
cubrid_fetch_object: the row array used to build the object is released
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
class Row {
    public $lv;
}

$req = cubrid_execute($conn, "select level as lv from db_root connect by level <= 2000");
cubrid_fetch_object($req);
$before = memory_get_usage();
for ($i = 0; $i < 1000; $i++) {
    $obj = cubrid_fetch_object($req);
}
$growth = memory_get_usage() - $before;
printf("stdClass: %s\n", $growth < 4096 ? "no leak" : "leaked $growth bytes");

$before = memory_get_usage();
for ($i = 0; $i < 999; $i++) {
    $obj = cubrid_fetch_object($req, "Row");
}
$growth = memory_get_usage() - $before;
printf("Row: %s\n", $growth < 4096 ? "no leak" : "leaked $growth bytes");
var_dump($obj);

cubrid_close_request($req);
cubrid_disconnect($conn);
print "done!\n";
?>
--CLEAN--
--EXPECTF--
stdClass: no leak
Row: no leak
object(Row)#%d (1) {
  ["lv"]=>
  string(4) "2000"
}
done!
