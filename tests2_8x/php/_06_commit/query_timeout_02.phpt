--TEST--
cubrid_get_query_timeout cubrid_set_query_timeout
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php

include_once('connect.inc');
printf("#####correct example#####\n");
$conn = cubrid_connect_with_url("CUBRID:$host:$port:$db:::?login_timeout=5000&query_timeout=5000&disconnect_on_query_timeout=yes");
cubrid_execute($conn, "DROP TABLE if exists timeout2_tb");
cubrid_execute($conn, "create table timeout2_tb(id int, name varchar(10))");
cubrid_execute($conn, "insert into timeout2_tb values(1,'nameq'),(2,'name2'),(3,'name3')");

$req1 = cubrid_execute($conn, "SELECT * FROM timeout2_tb");
$timeout1 = cubrid_get_query_timeout($req1);
printf("[001]timeout: %d\n",$timeout1);

cubrid_set_query_timeout($req1, 10);
$timeout2 = cubrid_get_query_timeout($req1);
printf("[002]timeout: %d\n",$timeout2);


for($i=0;$i<3;$i++){
   printf("\nThe %d query\n",$i);
   sleep(11);
   $result=cubrid_fetch_assoc($req1);
   var_dump($result);
   $timeout = cubrid_get_query_timeout($req1);
   var_dump($timeout);
}
cubrid_close_prepare($req1);

printf("\n\n#####negative example#####\n");
$timeout3 = cubrid_get_query_timeout($req1);
if(FALSE == $timeout3){
   printf("[003]Expect false,[%d] [%d] [%s]\n",cubrid_error_code_facility(), cubrid_errno($conn), cubrid_error($conn));
}else{
   var_dump($timeout3);
}


$req1 = cubrid_execute($conn, "SELECT * FROM timeout2_tb");
try {
    $timeout4 = cubrid_get_query_timeout($req1,'');
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $timeout4 = NULL;
}
if(FALSE == $timeout4){
   printf("[004]Expect false,[%d] [%d] [%s]\n",cubrid_error_code_facility(), cubrid_errno($conn), cubrid_error($conn));
}else{
   var_dump($timeout4);
}

try {
    $timeout5 = cubrid_get_query_timeout('');
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $timeout5 = NULL;
}
if(FALSE == $timeout5){
   printf("[005]Expect false,[%d] [%d] [%s]\n",cubrid_error_code_facility(), cubrid_errno($conn), cubrid_error($conn));
}else{
   var_dump($timeout5);
}

try {
    $timeout6 = cubrid_get_query_timeout('');
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $timeout6 = NULL;
}
if(FALSE == $timeout6){
   printf("[006]Expect false,[%d] [%d] [%s]\n",cubrid_error_code_facility(), cubrid_errno($conn), cubrid_error($conn));
}else{
   var_dump($timeout6);
}

$set7=cubrid_set_query_timeout($req1,'10');
if(FALSE == $set7){
   printf("[007]Expect false,[%d] [%d] [%s]\n",cubrid_error_code_facility(), cubrid_errno($conn), cubrid_error($conn));
}else{
   $timeout7 = cubrid_get_query_timeout($req1);
   var_dump($timeout7);
}

try {
    $set8=cubrid_set_query_timeout($req1,'');
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $set8 = NULL;
}
if(FALSE == $set8){
   printf("[008]Expect false,[%d] [%d] [%s]\n",cubrid_error_code_facility(), cubrid_errno($conn), cubrid_error($conn));
}else{
   $timeout8 = cubrid_get_query_timeout($req1);
   var_dump($timeout8);
}

$set9=cubrid_set_query_timeout($req1,NULL);
if(FALSE == $set9){
   printf("[009]Expect false,[%d] [%d] [%s]\n",cubrid_error_code_facility(), cubrid_errno($conn), cubrid_error($conn));
}else{
   $timeout9 = cubrid_get_query_timeout($req1);
   var_dump($timeout9);
}

try {
    $set10=cubrid_set_query_timeout($req1);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $set10 = NULL;
}
if(FALSE == $set10){
   printf("[0010]Expect false,[%d] [%d] [%s]\n",cubrid_error_code_facility(), cubrid_errno($conn), cubrid_error($conn));
}else{
   $timeout10 = cubrid_get_query_timeout($req1);
   var_dump($timeout10);
}

try {
    $set11=cubrid_set_query_timeout();
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $set11 = NULL;
}
if(FALSE == $set11){
   printf("[0011]Expect false,[%d] [%d] [%s]\n",cubrid_error_code_facility(), cubrid_errno($conn), cubrid_error($conn));
}else{
   $timeout11 = cubrid_get_query_timeout($req1);
   var_dump($timeout11);
}



cubrid_close_prepare($req1);
cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####correct example#####
[001]timeout: 5000
[002]timeout: 10

The 0 query
array(2) {
  ["id"]=>
  string(1) "1"
  ["name"]=>
  string(5) "nameq"
}
int(10)

The 1 query
array(2) {
  ["id"]=>
  string(1) "2"
  ["name"]=>
  string(5) "name2"
}
int(10)

The 2 query
array(2) {
  ["id"]=>
  string(1) "3"
  ["name"]=>
  string(5) "name3"
}
int(10)


#####negative example#####

Warning: cubrid_get_query_timeout(): supplied resource is not a valid CUBRID Request resource in %s on line %d
[003]Expect false,[0] [0] []
cubrid_get_query_timeout() expects exactly 1 argument, 2 given
[004]Expect false,[0] [0] []
cubrid_get_query_timeout(): Argument #1 ($req_id) must be of type resource, string given
[005]Expect false,[0] [0] []
cubrid_get_query_timeout(): Argument #1 ($req_id) must be of type resource, string given
[006]Expect false,[0] [0] []
int(10)
cubrid_set_query_timeout(): Argument #2 ($timeout) must be of type int, string given
[008]Expect false,[0] [0] []

Deprecated: cubrid_set_query_timeout(): Passing null to parameter #2 ($timeout) of type int is deprecated in %s on line %d
int(0)
cubrid_set_query_timeout() expects exactly 2 arguments, 1 given
[0010]Expect false,[0] [0] []
cubrid_set_query_timeout() expects exactly 2 arguments, 0 given
[0011]Expect false,[0] [0] []
Finished!
