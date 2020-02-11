--TEST--
cubrid_schema
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn = cubrid_connect($host, $port, "demodb","public","");
printf("\nCUBRID_SCH_ATTR_PRIVILEGE\n");
$schema4 = cubrid_schema($conn, CUBRID_SCH_ATTR_PRIVILEGE,"db_auth","auth_type");
var_dump($schema4);

cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
CUBRID_SCH_ATTR_PRIVILEGE
array(1) {
  [0]=>
  array(3) {
    ["ATTR_NAME"]=>
    string(9) "auth_type"
    ["PRIVILEGE"]=>
    string(6) "SELECT"
    ["GRANTABLE"]=>
    string(2) "NO"
  }
}
Finished!
