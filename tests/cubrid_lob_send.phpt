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

cubrid_execute($conn,"DROP TABLE if exists doc");
cubrid_execute($conn,"CREATE TABLE doc (id INT, doc_content CLOB)");
cubrid_execute($conn,"INSERT INTO doc VALUES (5,'hello,cubrid')");

$lobs = cubrid_lob_get($conn, "SELECT doc_content FROM doc WHERE id=5");

//error
cubrid_lob_send($conn);
//
cubrid_lob_send($conn, $lobs[0]);
cubrid_lob_close($lobs);
cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
Warning: cubrid_lob_send() expects exactly 2 parameters, 1 given %s
hello,cubriddone!
