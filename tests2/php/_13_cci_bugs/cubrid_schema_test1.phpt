--TEST--
cubrid_bind
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

$schema4 = cubrid_schema($conn, CUBRID_SCH_CLASS, "db_partition",'nothis attr_name');
if ($schema4 == false) {
    printf("[004] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value4: \n");
    var_dump($schema4);
}
cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
