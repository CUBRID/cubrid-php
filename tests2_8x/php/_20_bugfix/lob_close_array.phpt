--TEST--
cubrid_lob_close: closes every lob in the array instead of treating the array as a resource
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
cubrid_execute($conn, "drop table if exists bug_lob_close_tb");
cubrid_execute($conn, "create table bug_lob_close_tb (id int, c clob)");
cubrid_execute($conn, "insert into bug_lob_close_tb values (1, CHAR_TO_CLOB('abc')), (2, CHAR_TO_CLOB('defg'))");

$lobs = cubrid_lob_get($conn, "select c from bug_lob_close_tb order by id");
var_dump(cubrid_lob_size($lobs[0]), cubrid_lob_size($lobs[1]));

var_dump(cubrid_lob_close($lobs));
// The lobs must be closed now.
var_dump(cubrid_lob_size($lobs[0]));
var_dump(cubrid_lob_size($lobs[1]));
var_dump(count($lobs));

// Elements that are not lob resources
var_dump(cubrid_lob_close(array(1, 2)));

cubrid_execute($conn, "drop table if exists bug_lob_close_tb");
cubrid_disconnect($conn);
print "done!\n";
?>
--CLEAN--
--EXPECTF--
string(1) "3"
string(1) "4"
bool(true)

Warning: cubrid_lob_size(): supplied resource is not a valid CUBRID Lob resource in %s on line %d
bool(false)

Warning: cubrid_lob_size(): supplied resource is not a valid CUBRID Lob resource in %s on line %d
bool(false)
int(2)

Warning: cubrid_lob_close(): supplied resource is not a valid CUBRID Lob resource in %s on line %d
bool(false)
done!
