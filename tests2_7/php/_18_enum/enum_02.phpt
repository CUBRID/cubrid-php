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
$sql = "drop class if exists enum012";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

//create the class
$sql = "create class enum012(i INT,working_days ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday') not null,answers ENUM('Yes', 'No', 'Cancel'))";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

//insert values into the class
$sql = "insert into enum012 values(1,1,1)";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

//select all data default 
$sql = "select * from enum012";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
print("default: \n");
printf("%-40s %-20s %-20s %-40s\n", "column_name", "column_type", "column_len", "column_value");
while($row = cubrid_fetch_row($req)){
for($i = 0, $size = count($column_names1); $i < $size; $i++) {
     $column_len1 = cubrid_field_len($req, $i);
    printf("%-40s %-20s %-20s %-40s\n", $column_names1[$i], $column_types1[$i], $column_len1, $row[$i]);
}
}

//select enum data which is casted into int
$sql = "select cast(working_days as int), cast(answers as int) from enum012";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
print("Enum to int: \n");
printf("%-40s %-20s %-20s %-40s\n", "column_name", "column_type", "column_len", "column_value");
while($row = cubrid_fetch_row($req)){
for($i = 0, $size = count($column_names1); $i < $size; $i++) {
     $column_len1 = cubrid_field_len($req, $i);
    printf("%-40s %-20s %-20s %-40s\n", $column_names1[$i], $column_types1[$i], $column_len1, $row[$i]);
}
}


// select column i
$sql = "select i from enum012";
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
--EXPECT--
default: 
column_name                              column_type          column_len           column_value                            
i                                        integer              11                   1                                       
working_days                             enum                 0                    Monday                                  
answers                                  enum                 0                    Yes                                     
Enum to int: 
column_name                              column_type          column_len           column_value                            
cast(working_days as integer)            integer              11                   1                                       
cast(answers as integer)                 integer              11                   1                                       
column_name                              column_type          column_len           column_value                            
i                                        integer              11                   1                                       
Finished
