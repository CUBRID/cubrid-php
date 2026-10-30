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

$sql = "drop class if exists escape01";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

$unescaped_str = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~';
$escaped_str = cubrid_real_escape_string($unescaped_str);

$len = strlen($unescaped_str);

$sql = "create class escape01(i INT, working_days ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), answers ENUM('Yes', 'No', 'Cancel'), t char($len))";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

$sql = "insert into escape01 values(1,1,1,'$escaped_str'),(2,'Tuesday','No','$escaped_str'), (3, 'Wednesday','Cancel','$escaped_str')";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);
#$req = cubrid_execute($conn, $sql);

$sql = "select * from escape01";
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
column_name                              column_type          column_len           column_value                            
i                                        integer              11                   1                                       
working_days                             enum                 0                    Monday                                  
answers                                  enum                 0                    Yes                                     
t                                        char                 95                    !"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~
i                                        integer              11                   2                                       
working_days                             enum                 0                    Tuesday                                 
answers                                  enum                 0                    No                                      
t                                        char                 95                    !"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~
i                                        integer              11                   3                                       
working_days                             enum                 0                    Wednesday                               
answers                                  enum                 0                    Cancel                                  
t                                        char                 95                    !"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~
Finished
