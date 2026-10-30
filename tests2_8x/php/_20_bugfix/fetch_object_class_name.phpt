--TEST--
cubrid_fetch_object: class name that is not a string
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
$req = cubrid_execute($conn, "select 1 as id from db_root");

var_dump(cubrid_fetch_object($req, 123, array()));
var_dump(cubrid_fetch_object($req, array(), array(), CUBRID_LOB));
var_dump(cubrid_fetch_object($req));

cubrid_close_request($req);
cubrid_disconnect($conn);
print "done!\n";
?>
--CLEAN--
--EXPECTF--
Warning: cubrid_fetch_object(): Class name must be a string in %s on line %d
NULL

Warning: cubrid_fetch_object(): Class name must be a string in %s on line %d
NULL
object(stdClass)#%d (1) {
  ["id"]=>
  string(1) "1"
}
done!
