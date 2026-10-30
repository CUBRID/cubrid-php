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
$sql = "drop class if exists enum02";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

//create the class
$sql = "create class enum02(i INT AUTO_INCREMENT, working_days ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),answers ENUM('Yes', 'No', 'Cancel'))";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

//insert 
$days = array("Monday", "Tuesday", "Wednesday");
$answers = array("Yes", "No", "Cancel");
$sql = "insert into enum02(working_days, answers) values(?,?)";
for($i=0; $i<3; $i++){
   $req = cubrid_prepare($conn, $sql, CUBRID_INCLUDE_OID);
   cubrid_bind($req, 1, $days[$i] );
   cubrid_bind($req, 2, $answers[$i] );
   cubrid_execute($req);
}

// select
$sql = "select * from enum02";
$req = cubrid_prepare($conn, $sql, CUBRID_INCLUDE_OID);
cubrid_execute($req);
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

//select data
$sql = "select cast(working_days as int), cast(answers as int) from enum02";
$req = cubrid_prepare($conn, $sql, CUBRID_INCLUDE_OID);
cubrid_execute($req);
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
*****************************************
i                             working_days                  answers                       
1                             Monday                        Yes                           
2                             Tuesday                       No                            
3                             Wednesday                     Cancel                        
*****************************************
cast(working_days as integer) cast(answers as integer)      
1                             1                             
2                             2                             
3                             3                             
Finished
