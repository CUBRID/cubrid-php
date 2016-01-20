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
$db_list3 = cubrid_list_dbs("nothisparameter");
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

$db4=cubrid_db_name($db_list,2);
if(FALSE == $db4){
   printf("[004]Expect false, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("[004]cubrid_db_name: %s\n",$db4);
}

$db5=cubrid_db_name();
if(FALSE == $db5){
   printf("[005]Expect false, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("[005]cubrid_db_name: %s\n",$db5);
}

$db6=cubrid_db_name($db_list, "nothisindex");
if(FALSE == $db6){
   printf("[006]Expect false, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("[006]cubrid_db_name: %s\n",$db6);
}

$db7=cubrid_db_name($db_list);
if(FALSE == $db7){
   printf("[007]Expect false, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("[007]cubrid_db_name: %s\n",$db7);
}

$db_array=array("qadb","demodb");
$db8=cubrid_db_name($db_array,0);
if(FALSE == $db8){
   printf("[008]Expect false, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("[008]cubrid_db_name: %s\n",$db8);
}

$db9=cubrid_db_name("nothisarray",0);
if(FALSE == $db9){
   printf("[009]Expect false, [%d] [%s]\n",cubrid_errno($conn),cubrid_error($conn));
}else{
   printf("[009]cubrid_db_name: %s\n",$db9);
}






cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--XFAIL--
--EXPECTF--
#####positive example#####
array(2) {
  [0]=>
  string(4) "qadb"
  [1]=>
  string(6) "demodb"
}
#####cubrid_db_name#####
qadb
demodb


#####negative example for cubrid_list_dbs#####

Warning: cubrid_list_dbs() expects parameter 1 to be resource, string given in %s on line %d
[002]Expect false, [0] []


#####negative example for cubrid_db_name#####
[003]Expect false, [0] []
[004]Expect false, [0] []

Warning: cubrid_db_name() expects exactly 2 parameters, 0 given in %s on line %d
[005]Expect false, [0] []

Warning: cubrid_db_name() expects parameter 2 to be long, string given in %s on line %d
[006]Expect false, [0] []

Warning: cubrid_db_name() expects exactly 2 parameters, 1 given in %s on line %d
[007]Expect false, [0] []
[008]cubrid_db_name: qadb

Warning: cubrid_db_name() expects parameter 1 to be array, string given in %s on line %d
[009]Expect false, [0] []
Finished!
