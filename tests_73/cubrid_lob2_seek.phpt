--TEST--
cubrid_lob2_seek
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php

include_once('connect.inc');

$tmp = NULL;
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

if (!is_null($tmp = @cubrid_lob2_seek())) {
    printf('[001] Expecting NULL, got %s/%s\n', gettype($tmp), $tmp);
}

@cubrid_execute($conn, 'DROP TABLE IF EXISTS test_lob2');
cubrid_execute($conn, 'CREATE TABLE test_lob2 (id INT, contents CLOB)');

$req = cubrid_prepare($conn, 'INSERT INTO test_lob2 VALUES (?, ?)');

cubrid_bind($req, 1, 10);
cubrid_lob2_bind($req, 2, "Wow, welcome to CUBRID! You are using CLOB now!", "CLOB");

cubrid_execute($req);

$req = cubrid_execute($conn, "SELECT * FROM test_lob2");

$row = cubrid_fetch_row($req, CUBRID_LOB);

$lob = $row[1];

$size = cubrid_lob2_size($lob);
$size64 = cubrid_lob2_size64($lob);

print "cubrid_lob2_size : $size\n";

cubrid_lob2_seek($lob, $size, CUBRID_CURSOR_FIRST);

$position = cubrid_lob2_tell($lob);
print "position after move $size related to CUBRID_CURSOR_FIRST: $position\n";

if (false !== ($tmp = cubrid_lob2_seek($lob, $size + 1, CUBRID_CURSOR_FIRST))) {
    printf("[002] Expecting boolean/false, got %s/%s\n", gettype($tmp), $tmp);
}

cubrid_lob2_seek($lob, $size, CUBRID_CURSOR_LAST);

$position = cubrid_lob2_tell($lob);
print "position after move $size related to CUBRID_CURSOR_LAST: $position\n";

if (false !== ($tmp = cubrid_lob2_seek($lob, -1 , CUBRID_CURSOR_CURRENT))) {
    printf("[003] Expecting boolean/false, got %s/%s\n", gettype($tmp), $tmp);
}

cubrid_lob2_seek($lob, $size - 20, CUBRID_CURSOR_CURRENT);

$position = cubrid_lob2_tell($lob);
print "position after move " . ($size - 20) . " related to CUBRID_CURSOR_CURRENT: $position\n";

cubrid_lob2_seek64($lob, "16", CUBRID_CURSOR_FIRST);
$position = cubrid_lob2_tell64($lob);
$size64=cubrid_lob2_size64($lob);

cubrid_lob2_close($lob);
//error
cubrid_lob2_close($lob);
cubrid_lob2_close(0);
cubrid_lob2_size64(0);
cubrid_lob2_tell64(0);
cubrid_lob2_seek64($lob);
cubrid_lob2_seek64($lob, "16", CUBRID_CURSOR_FIRST);
cubrid_lob2_tell64($lob);
cubrid_lob2_size64($lob);

cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
cubrid_lob2_size : 47
position after move 47 related to CUBRID_CURSOR_FIRST: 47

Warning: cubrid_lob2_seek(): offset(48) is not correct, it can't be a negative number or larger than size in /home/phppdo_release/exec/phpTest/cubrid-php/tests_73/cubrid_lob2_seek.php on line 38
position after move 47 related to CUBRID_CURSOR_LAST: 0

Warning: cubrid_lob2_seek(): offet(-1) is out of range, please input a proper number. in /home/phppdo_release/exec/phpTest/cubrid-php/tests_73/cubrid_lob2_seek.php on line 47
position after move 27 related to CUBRID_CURSOR_CURRENT: 27

Warning: cubrid_lob2_close() expects parameter 1 to be resource, int given in /home/phppdo_release/exec/phpTest/cubrid-php/tests_73/cubrid_lob2_seek.php on line 63

Warning: cubrid_lob2_size64() expects parameter 1 to be resource, int given in /home/phppdo_release/exec/phpTest/cubrid-php/tests_73/cubrid_lob2_seek.php on line 64

Warning: cubrid_lob2_tell64() expects parameter 1 to be resource, int given in /home/phppdo_release/exec/phpTest/cubrid-php/tests_73/cubrid_lob2_seek.php on line 65

Warning: cubrid_lob2_seek64() expects at least 2 parameters, 1 given in /home/phppdo_release/exec/phpTest/cubrid-php/tests_73/cubrid_lob2_seek.php on line 66
done!
