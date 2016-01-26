--TEST--
cubrid_lob2_seek64
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php

include_once('connect.inc');
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

@cubrid_execute($conn, 'DROP TABLE IF EXISTS test_lob');
cubrid_execute($conn, 'CREATE TABLE test_lob (id INT, contents CLOB)');

$req = cubrid_prepare($conn, "INSERT INTO test_lob VALUES(2, ?)");

$lob = cubrid_lob2_new($conn, 'CLOB');
$len = cubrid_lob2_write($lob, "Hello world");

cubrid_lob2_bind($req, 1, $lob);
cubrid_execute($req);

$req = cubrid_execute($conn, "select * from test_lob");
$row = cubrid_fetch_row($req, CUBRID_LOB);
$lob = $row[1];

cubrid_lob2_seek64($lob, "20101029056306120215", CUBRID_CURSOR_FIRST);
$data = cubrid_lob2_read($lob, 20);

cubrid_lob2_seek64($lob, "-1", CUBRID_CURSOR_FIRST);
$data = cubrid_lob2_read($lob, 20);

cubrid_lob2_seek64($lob, "3", CUBRID_CURSOR_FIRST);
$data = cubrid_lob2_read($lob, 20);
var_Dump($data);

cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
Warning: cubrid_lob2_seek64(): offset(20101029056306120215) is out of range for the lob field you have chosen, so please check the offset you give and the lob length. in %s on line %d

Warning: cubrid_lob2_seek64(): offset(-1) must not be a negative number, so please check the offset you give. in %s on line %d
string(8) "lo world"
done!

