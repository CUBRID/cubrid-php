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
$sql = "drop class if exists t1";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

//create the class
$sql = "create table t1(e1 enum ('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),e2 enum('02/23/2012', '12/21/2012'), e3 enum('11:12:09', '13:13:13'), e4 enum('123', '9876', '-34'))";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

//insert 
$sql = "insert into t1 values(2, 1, 1, 2), (5, 2, 1, 1), (6, 2, 2, 3),(1, 1, 1, 2), (7, 1, 2, 3), (4, 2, 2, 2), (3, 1, 1, 1)";
$req = cubrid_execute($conn, $sql, CUBRID_INCLUDE_OID);

//select bind
print("*****************************************\n");
$sql = "select e1 + ?, ? + e1, e1 + ?, e1 * ?, e1 + ? from t1 where e1 < ? order by 1, 2, 3, 4, 5";
$a=1;
$b=1;
$c=1.1; 
$d=5;
//$e=2;
$e='-';
$f=7;   
$req = cubrid_prepare($conn, $sql);

/*
cubrid_bind($req, 1, $a );
cubrid_bind($req, 2, $b );
cubrid_bind($req, 3, $c );
cubrid_bind($req, 4, $d );
cubrid_bind($req, 5, $e );
cubrid_bind($req, 6, $f );
*/

cubrid_bind($req, 1, $a);
cubrid_bind($req, 2, $b);
cubrid_bind($req, 3, $c);
cubrid_bind($req, 4, $d );
cubrid_bind($req, 5, $e);
cubrid_bind($req, 6, $f, "number" );

cubrid_execute($req);

$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
for($i = 0; $i < $size; $i++) {
printf("%-40s", $column_names1[$i]);
}
print("\n");
while($row = cubrid_fetch_row($req)){
for($i = 0; $i < $size; $i++) {
   printf("%-40s", $row[$i]);
}
print("\n");
}

//select data
print("*****************************************\n");
$sql = "select repeat(e1, ?), substring(e1, ?, ?), concat(e1,?, e2, ?, e3), repeat(?, e1) from t1 order by 1, 2, 3, 4";
$req = cubrid_prepare($conn, $sql, CUBRID_INCLUDE_OID);
cubrid_bind($req, 1, 2);
cubrid_bind($req, 2, 2 );
cubrid_bind($req, 3, 4 );
cubrid_bind($req, 4, "contact1" );
cubrid_bind($req, 5, "contact2");
cubrid_bind($req, 6, "repeat_value" );
cubrid_execute($req);
$column_names1 = cubrid_column_names($req);
$column_types1 = cubrid_column_types($req);
$size = count($column_names1);
for($i = 0; $i < $size; $i++) {
printf("%-100s", $column_names1[$i]);
}
print("\n");
while($row = cubrid_fetch_row($req)){
for($i = 0; $i < $size; $i++) {
   printf("%-100s", $row[$i]);
}
print("\n");
}

cubrid_disconnect($conn);
print "Finished\n";
?>
--CLEAN--
--EXPECT--
*****************************************
e1+ ?:0                                 ?:1 +e1                                 e1+ ?:2                                 e1* ?:3                                 e1+ ?:4                                 
Friday1                                 1Friday                                 Friday1.1                               30.0000000000000000                     Friday-                                 
Monday1                                 1Monday                                 Monday1.1                               10.0000000000000000                     Monday-                                 
Sunday1                                 1Sunday                                 Sunday1.1                               5.0000000000000000                      Sunday-                                 
Thursday1                               1Thursday                               Thursday1.1                             25.0000000000000000                     Thursday-                               
Tuesday1                                1Tuesday                                Tuesday1.1                              15.0000000000000000                     Tuesday-                                
Wednesday1                              1Wednesday                              Wednesday1.1                            20.0000000000000000                     Wednesday-                              
*****************************************
repeat(e1,  ?:0 )                                                                                   substring(e1 from  ?:1  for  ?:2 )                                                                  concat(e1,  ?:3 , e2,  ?:4 , e3)                                                                    repeat( ?:5 , e1)                                                                                   
FridayFriday                                                                                        rida                                                                                                Fridaycontact112/21/2012contact213:13:13                                                            repeat_valuerepeat_valuerepeat_valuerepeat_valuerepeat_valuerepeat_value                            
MondayMonday                                                                                        onda                                                                                                Mondaycontact102/23/2012contact211:12:09                                                            repeat_valuerepeat_value                                                                            
SaturdaySaturday                                                                                    atur                                                                                                Saturdaycontact102/23/2012contact213:13:13                                                          repeat_valuerepeat_valuerepeat_valuerepeat_valuerepeat_valuerepeat_valuerepeat_value                
SundaySunday                                                                                        unda                                                                                                Sundaycontact102/23/2012contact211:12:09                                                            repeat_value                                                                                        
ThursdayThursday                                                                                    hurs                                                                                                Thursdaycontact112/21/2012contact211:12:09                                                          repeat_valuerepeat_valuerepeat_valuerepeat_valuerepeat_value                                        
TuesdayTuesday                                                                                      uesd                                                                                                Tuesdaycontact102/23/2012contact211:12:09                                                           repeat_valuerepeat_valuerepeat_value                                                                
WednesdayWednesday                                                                                  edne                                                                                                Wednesdaycontact112/21/2012contact213:13:13                                                         repeat_valuerepeat_valuerepeat_valuerepeat_value                                                    
Finished
