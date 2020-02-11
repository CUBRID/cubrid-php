--TEST--
enum type
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
cubrid_set_autocommit($conn, false);

$sql = "drop class if exists enum01";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);
cubrid_commit($conn);

$sql = "create class enum01(i INT, working_days ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), answers ENUM('Yes', 'No', 'Cancel'))";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);
cubrid_commit($conn);

$sql = "insert into enum01 values(3, 'Wednesday','Cancel'),(2,'Tuesday','No'),(1,1,1)";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

$sql = "select * from enum01";
$req_holdability = cubrid_prepare($conn, $sql, CUBRID_INCLUDE_OID);
cubrid_execute($req_holdability);
$column_names1 = cubrid_column_names($req_holdability);
$column_types1 = cubrid_column_types($req_holdability);
$size = count($column_names1);
//fetch all
print("\n**********************Fetch all************************\n");
$row_size = 0;
while($row = cubrid_fetch_row($req_holdability)){
    $row_size++;
for($i = 0; $i < $size; $i++) {
   printf("%-30s", $row[$i]);
}
print("\n");
}
print("row_size: ".$row_size."\n");

cubrid_disconnect($conn);
print "Finished\n";
?>
--CLEAN--
--EXPECT--
**********************Fetch all************************
3                             Wednesday                     Cancel                        
2                             Tuesday                       No                            
1                             Monday                        Yes                           
row_size: 3
Finished
