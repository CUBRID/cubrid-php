--TEST--
cubrid_list_dbs cubrid_db_name
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect_with_url($connect_url, $user, $passwd);
if (!$conn) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
    exit(1);
}
printf("#####positive example#####\n");
$db_list = cubrid_list_dbs($conn);
var_dump($db_list);

$db_list=cubrid_list_dbs($conn);
$i = 0;
$cnt =count($db_list);
printf("#####cubrid_db_name#####\n");
while($i < $cnt) {
    echo cubrid_db_name($db_list, $i) . "\n";
    $i++;
}

printf("\n\n#####negative example for cubrid_list_dbs#####\n");
try {
    $db_list3 = cubrid_list_dbs("nothisparameter");
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $db_list3 = NULL;
}
if(FALSE == $db_list3){
   printf("[002]Expect false, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("[002]db_list3: %s\n",$db_list3);
}

printf("\n\n#####negative example for cubrid_db_name#####\n");
//index out of range
$db3=cubrid_db_name($db_list, -1);
if(FALSE == $db3){
   printf("[003]Expect false, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("[003]cubrid_db_name: %s\n",$db3);
}

$db4=cubrid_db_name($db_list,3);
if(FALSE == $db4){
   printf("[004]Expect false, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("[004]cubrid_db_name: %s\n",$db4);
}

try {
    $db5=cubrid_db_name();
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $db5 = NULL;
}
if(FALSE == $db5){
   printf("[005]Expect false, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("[005]cubrid_db_name: %s\n",$db5);
}

try {
    $db6=cubrid_db_name($db_list, "nothisindex");
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $db6 = NULL;
}
if(FALSE == $db6){
   printf("[006]Expect false, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("[006]cubrid_db_name: %s\n",$db6);
}

try {
    $db7=cubrid_db_name($db_list);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $db7 = NULL;
}
if(FALSE == $db7){
   printf("[007]Expect false, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("[007]cubrid_db_name: %s\n",$db7);
}

$db_array=array("phpdb","demodb");
$db8=cubrid_db_name($db_array,0);
if(FALSE == $db8){
   printf("[008]Expect false, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("[008]cubrid_db_name: %s\n",$db8);
}

try {
    $db9=cubrid_db_name("nothisarray",0);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $db9 = NULL;
}
if(FALSE == $db9){
   printf("[009]Expect false, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("[009]cubrid_db_name: %s\n",$db9);
}






cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
array(3) {
  [0]=>
  string(5) "phpdb"
  [1]=>
  string(7) "largedb"
  [2]=>
  string(6) "demodb"
}
#####cubrid_db_name#####
phpdb
largedb
demodb


#####negative example for cubrid_list_dbs#####
cubrid_list_dbs(): Argument #1 ($conn_id) must be of type resource, string given
[002]Expect false, [0] []


#####negative example for cubrid_db_name#####
[003]Expect false, [0] []
[004]Expect false, [0] []
cubrid_db_name() expects exactly 2 arguments, 0 given
[005]Expect false, [0] []
cubrid_db_name(): Argument #2 ($row) must be of type int, string given
[006]Expect false, [0] []
cubrid_db_name() expects exactly 2 arguments, 1 given
[007]Expect false, [0] []
[008]cubrid_db_name: phpdb
cubrid_db_name(): Argument #1 ($result) must be of type array, string given
[009]Expect false, [0] []
Finished!
