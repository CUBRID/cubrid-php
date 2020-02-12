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

$sql = "create class enum01(i INT, working_days ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), answers ENUM('Yes', 'No', 'Cancel'))";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

$sql = "insert into enum01 values(1,1,1),(2,'Tuesday','No'), (3, 'Wednesday','Cancel')";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);
cubrid_commit($conn);

$sql = "select * from enum01";
$req_holdability = cubrid_prepare($conn, $sql, CUBRID_INCLUDE_OID);
cubrid_execute($req_holdability);
$column_names1 = cubrid_column_names($req_holdability);
$column_types1 = cubrid_column_types($req_holdability);
$size = count($column_names1);
print("*******************First select****************\n");
for($i = 0; $i < $size; $i++) {
printf("%-30s", $column_names1[$i]);
}
print("\n");
$row = cubrid_fetch_row($req_holdability);
for($i = 0; $i < $size; $i++) {
   printf("%-30s", $row[$i]);
}
cubrid_rollback($conn);
print("\n");

$sql = "insert into enum01 values(1,1,1),(2,'Tuesday','No'), (3, 'Wednesday','Cancel')";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);
cubrid_commit($conn);

$column_names1 = cubrid_column_names($req_holdability);
$column_types1 = cubrid_column_types($req_holdability);
$size = count($column_names1);
print("*******************Second select****************\n");
for($i = 0; $i < $size; $i++) {
printf("%-30s", $column_names1[$i]);
}
//fetch next
print("\n");
$row = cubrid_fetch_row($req_holdability);
for($i = 0; $i < $size; $i++) {
   printf("%-30s", $row[$i]);
}
print("\n");
print("**********************Fetch all************************\n");
//fetch all
$row_size = 2;
while($row = cubrid_fetch_row($req_holdability)){
    $row_size++;
for($i = 0; $i < $size; $i++) {
   printf("%-30s", $row[$i]);
}
print("\n");
}
print("row_size: ".$row_size."\n");


cubrid_execute($req_holdability);
for($i = 0; $i < $size; $i++) {
printf("%-30s", $column_names1[$i]);
}

//fetch all
print("\n**********************Fetch all************************\n");
$row_size = 0;
while($row = cubrid_fetch_row($req_holdability)){
    $row_size++;
for($i = 0; $i < $size; $i++) {
   printf("%-30s", $row[$i]);
}
cubrid_commit($conn);
print("\n");
}
print("row_size: ".$row_size."\n");

cubrid_commit($conn);
cubrid_disconnect($conn);
print "Finished\n";
?>
--CLEAN--
--EXPECTF--
*******************First select****************
i                             working_days                  answers                       
1                             Monday                        Yes                           
*******************Second select****************
i                             working_days                  answers                       

Warning: Error: CCI, -20040, Result set is closed in %s on line %d

Notice: Trying to access array offset on value of type null in %s on line %d
                              
Notice: Trying to access array offset on value of type null in %s on line %d
                              
Notice: Trying to access array offset on value of type null in %s on line %d
                              
**********************Fetch all************************

Warning: Error: CCI, -20040, Result set is closed in %s on line %d
row_size: 2
i                             working_days                  answers                       
**********************Fetch all************************
1                             Monday                        Yes                           
2                             Tuesday                       No                            
3                             Wednesday                     Cancel                        
1                             Monday                        Yes                           
2                             Tuesday                       No                            
3                             Wednesday                     Cancel                        
row_size: 6
Finished
