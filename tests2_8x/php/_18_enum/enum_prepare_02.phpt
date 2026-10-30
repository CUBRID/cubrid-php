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

//drop the class if exist
$sql = "drop table if exists enum03";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

//create the class
$sql = "create table enum03(e1 enum('a', 'b'), e2 enum('Yes', 'No', 'Cancel'),e3 enum ('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), e4 enum('x', 'y', 'z'))";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

//insert 
$sql = "insert into enum03 values(1,1,1,1), (2, 3, 7, 3), ('b', 'No', 'Tuesday', 'y'),('a', 'Yes', 'Friday', 'x'), ('a', 'Cancel', 'Thursday', 'z'),('b', 1, 4, 'z')";
$req = cubrid_prepare($conn, $sql, CUBRID_INCLUDE_OID);
cubrid_execute($req);
// select
$sql = "select * from enum03 order by 1, 2, 3, 4";
$req = cubrid_prepare($conn, $sql, CUBRID_INCLUDE_OID);
cubrid_execute($req);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
print("**************************************\n");
for($i = 0; $i < $size; $i++) {
printf("%-30s", $column_names1[$i]);
}
print("\n");
while($row = cubrid_fetch_row($req)){
for($i = 0; $i < $size; $i++) {
   printf("%-30s", $row[$i]);
}
print("\n");
}

//prepare
$sql = "prepare x from 'select * from enum03 where e3 < ? and (e1 <> ? or e2 <> ?) order by 1, 2, 3, 4'";
$req = cubrid_prepare($conn, $sql, CUBRID_INCLUDE_OID);
cubrid_execute($req);

//execute
$sql = "execute x using 6, 2, 3";
cubrid_execute($conn, $sql);
if (!$req) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
print("*****************************************\n");
for($i = 0; $i < $size; $i++) {
printf("%-30s", $column_names1[$i]);
}
print("\n");
while($row = cubrid_fetch_row($req)){
for($i = 0; $i < $size; $i++) {
   printf("%-30s", $row[$i]);
}
print("\n");
}

$sql = "execute x using 'Sunday', 'a', 'Yes'";
cubrid_execute($conn, $sql);
if (!$req) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
print("*****************************************\n");
for($i = 0; $i < $size; $i++) {
printf("%-30s", $column_names1[$i]);
}
print("\n");
while($row = cubrid_fetch_row($req)){
for($i = 0; $i < $size; $i++) {
   printf("%-30s", $row[$i]);
}
print("\n");
}


//execute
$sql = "drop prepare x";
cubrid_execute($conn, $sql);
if (!$req) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
print("*****************************************\n");
for($i = 0; $i < $size; $i++) {
printf("%-30s", $column_names1[$i]);
}
print("\n");
while($row = cubrid_fetch_row($req)){
for($i = 0; $i < $size; $i++) {
   printf("%-30s", $row[$i]);
}
print("\n");
}

cubrid_disconnect($conn);
print "Finished\n";
?>
--CLEAN--
--EXPECT--
**************************************
e1                            e2                            e3                            e4                            
a                             Yes                           Sunday                        x                             
a                             Yes                           Friday                        x                             
a                             Cancel                        Thursday                      z                             
b                             Yes                           Wednesday                     z                             
b                             No                            Tuesday                       y                             
b                             Cancel                        Saturday                      z                             
*****************************************

*****************************************

*****************************************

Finished
