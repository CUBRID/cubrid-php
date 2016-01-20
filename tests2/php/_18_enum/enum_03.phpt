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

$sql = "drop table if exists enum0123";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

$sql = "create table enum0123(id int, e2 enum('Yes', 'No', 'Cancel'))";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

$sql = "insert into enum0123 values (1, 2),(2, 3)";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

$sql = "update enum0123 set e2=id";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);
$sql = "select e2 from enum0123";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
printf("%-40s %-20s %-20s %-40s\n", "column_name", "column_type", "column_len", "column_value");
while($row = cubrid_fetch_row($req)){
for($i = 0, $size = count($column_names1); $i < $size; $i++) {
     $column_len1 = cubrid_field_len($req, $i);
    printf("%-40s %-20s %-20s %-40s\n", $column_names1[$i], $column_types1[$i], $column_len1, $row[$i]);
}
}

//update the value out of the enum 
$sql = "update enum0123 set e2=id + 2";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);
$sql = "select e2 from enum0123";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
printf("%-40s %-20s %-20s %-40s\n", "column_name", "column_type", "column_len", "column_value");
while($row = cubrid_fetch_row($req)){
for($i = 0, $size = count($column_names1); $i < $size; $i++) {
     $column_len1 = cubrid_field_len($req, $i);
    printf("%-40s %-20s %-20s %-40s\n", $column_names1[$i], $column_types1[$i], $column_len1, $row[$i]);
}
}

$sql = "update enum0123 set id=cast(e2 as int)+40";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

$sql = "select * from enum0123 order by 1, 2";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
printf("%-40s %-20s %-20s %-40s\n", "column_name", "column_type", "column_len", "column_value");
while($row = cubrid_fetch_row($req)){
for($i = 0, $size = count($column_names1); $i < $size; $i++) {
     $column_len1 = cubrid_field_len($req, $i);
    printf("%-40s %-20s %-20s %-40s\n", $column_names1[$i], $column_types1[$i], $column_len1, $row[$i]);
}
}

cubrid_disconnect($conn);
print "Finished\n";
?>
--CLEAN--
--EXPECTF--
column_name                              column_type          column_len           column_value                            
e2                                       enum                 0                    Yes                                     
e2                                       enum                 0                    No                                      

Warning: Error: DBMS, -181, Cannot coerce value of domain "integer" to domain "enum".%s. in /home/php/cubrid_php/trunk/tests2/php/_18_enum/enum_03.php on line 30
column_name                              column_type          column_len           column_value                            
e2                                       enum                 0                    Yes                                     
e2                                       enum                 0                    No                                      
column_name                              column_type          column_len           column_value                            
id                                       integer              11                   41                                      
e2                                       enum                 0                    Yes                                     
id                                       integer              11                   42                                      
e2                                       enum                 0                    No                                      
Finished
