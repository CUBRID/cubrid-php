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
$timeout4 = cubrid_get_query_timeout($req1,'');
if(FALSE == $timeout4){
   printf("[004]Expect false,[%d] [%d] [%s]\n",cubrid_error_code_facility(), cubrid_errno($conn), cubrid_error($conn));
}else{
   var_dump($timeout4);
}

$timeout5 = cubrid_get_query_timeout('');
if(FALSE == $timeout5){
   printf("[005]Expect false,[%d] [%d] [%s]\n",cubrid_error_code_facility(), cubrid_errno($conn), cubrid_error($conn));
}else{
   var_dump($timeout5);
}

$timeout6 = cubrid_get_query_timeout('');
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

$set8=cubrid_set_query_timeout($req1,'');
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

$set10=cubrid_set_query_timeout($req1);
if(FALSE == $set10){
   printf("[0010]Expect false,[%d] [%d] [%s]\n",cubrid_error_code_facility(), cubrid_errno($conn), cubrid_error($conn));
}else{
   $timeout10 = cubrid_get_query_timeout($req1);
   var_dump($timeout10);
}

$set11=cubrid_set_query_timeout();
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
int(10)

Warning: cubrid_get_query_timeout() expects exactly 1 parameter, 2 given in %s on line %d
[004]Expect false,[0] [0] []

Warning: cubrid_get_query_timeout() expects parameter 1 to be resource, string given in %s on line %d
[005]Expect false,[0] [0] []

Warning: cubrid_get_query_timeout() expects parameter 1 to be resource, string given in %s on line %d
[006]Expect false,[0] [0] []
int(10)

Warning: cubrid_set_query_timeout() expects parameter 2 to be integer, string given in %s on line %d
[008]Expect false,[0] [0] []
int(0)

Warning: cubrid_set_query_timeout() expects exactly 2 parameters, 1 given in %s on line %d
[0010]Expect false,[0] [0] []

Warning: cubrid_set_query_timeout() expects exactly 2 parameters, 0 given in %s on line %d
[0011]Expect false,[0] [0] []
Finished!
