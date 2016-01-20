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
cubrid_execute($conn, 'DROP TABLE IF EXISTS blob_tb');
cubrid_execute($conn,"CREATE TABLE blob_tb(id int, c10 clob,c11 blob);");
cubrid_execute($conn,"insert into blob_tb values( 1, CHAR_TO_CLOB('This is a Dog'), BIT_TO_BLOB(X'000001'))");
$result=cubrid_execute($conn,"select id as int_t, c10, BLOB_TO_BIT(c11) from blob_tb");
$col_num = cubrid_num_cols($result);

for($i = 0; $i < $col_num; $i++) {
   printf("%-30s %s\n", cubrid_field_name($result, $i), cubrid_field_flags($result, $i)); 
}

cubrid_close_prepare($result);

cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--XFAIL--
http://jira.cubrid.org/browse/APIS-127
--EXPECTF--
int_t
c10
blob_to_bit(c11)
Finished!
