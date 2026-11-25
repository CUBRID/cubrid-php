--TEST--
cubrid_lob_get: lob memory is released when the lob resource is freed
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
cubrid_execute($conn, "drop table if exists bug_lob_leak_tb");
cubrid_execute($conn, "create table bug_lob_leak_tb (id int, c clob)");
cubrid_execute($conn, "insert into bug_lob_leak_tb values (1, CHAR_TO_CLOB('abc'))");

function get_and_free($conn, $close) {
    $lobs = cubrid_lob_get($conn, "select c from bug_lob_leak_tb");
    if ($close) {
        cubrid_lob_close($lobs);
    }
    unset($lobs);
}

foreach (array(false, true) as $close) {
    get_and_free($conn, $close);
    $before = memory_get_usage();
    for ($i = 0; $i < 500; $i++) {
        get_and_free($conn, $close);
    }
    $growth = memory_get_usage() - $before;
    printf("close=%s: %s\n", $close ? "yes" : "no", $growth < 4096 ? "no leak" : "leaked $growth bytes");
}

cubrid_execute($conn, "drop table if exists bug_lob_leak_tb");
cubrid_disconnect($conn);
print "done!\n";
?>
--CLEAN--
--EXPECTF--
close=no: no leak
close=yes: no leak
done!
