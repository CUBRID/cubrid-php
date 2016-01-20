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

cubrid_execute($conn,"drop table if EXISTS index_tb;");
cubrid_execute($conn,"CREATE TABLE index_tb(id INT PRIMARY KEY,phone VARCHAR(10),address string);");
cubrid_execute($conn,"create index index_tb_index on index_tb(address)");

$result=cubrid_execute($conn,"select * from  index_tb;");
$col_num = cubrid_num_cols($result);

for($i = 0; $i < $col_num; $i++) {
   printf("%-30s %s\n", cubrid_field_name($result, $i), cubrid_field_flags($result, $i)); 
}
cubrid_close_request($result);

cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
id                             not_null primary_key unique_key reverse_index
phone                          
address                        reverse_index
Finished!
