--TEST--
cubrid_put
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php
include_once("connect.inc");
define("GREETING","Hello world!");
$conn = cubrid_connect_with_url($connect_url, $user, $passwd);
if (!$conn) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
    exit(1);
}
cubrid_execute($conn, "drop table if exists put_tb2");
cubrid_execute($conn, "create table put_tb2(a int AUTO_INCREMENT, b set(int),c varchar(30))");
cubrid_execute($conn, "INSERT INTO put_tb2(a, b, c) VALUES (1, {1,2,3},'a')");

if (!$req = cubrid_execute($conn, "select * from put_tb2", CUBRID_INCLUDE_OID)) {
    printf("[002] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

$oid = cubrid_current_oid($req);
if (is_null ($oid)){
    printf("[003] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

cubrid_move_cursor($req, 1, CUBRID_CURSOR_FIRST);
$attr = cubrid_col_get($conn, $oid, "b");
var_dump($attr);

$put4=cubrid_put($conn, $oid, "b", array());
if(FALSE == $put4){
   printf("[004] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[004] \n");
   $attr = cubrid_col_get($conn, $oid, "b");
   var_dump($attr);
}

$put5= cubrid_put($conn, $oid,"c",NULL);
if(FALSE == $put5){
   printf("[005] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[005] \n");
   $attr = cubrid_get($conn, $oid, "c");
   var_dump($attr);
}

$put6= cubrid_put($conn, $oid,"c",constant("GREETING"));
if(FALSE == $put6){
   printf("[006] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[006] \n");
   $attr = cubrid_get($conn, $oid, "c");
   var_dump($attr);
}

$put7= cubrid_put($conn, $oid, array("a" =>NULL, "b" => array(7,8,9), "c" =>constant("GREETING")));
if(FALSE == $put7){
   printf("[007] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[007] \n");
   $attr1 = cubrid_get($conn, $oid, "b");
   var_dump($attr1);
   $attr = cubrid_get($conn, $oid, "c");
   var_dump($attr);
}

$put8= cubrid_put($conn, $oid, array("a" =>8, "b" => array(), "c" =>''));
if(FALSE == $put8){
   printf("[008] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[008] \n");
   $attr1 = cubrid_get($conn, $oid, "a");
   var_dump($attr1);
   $attr = cubrid_get($conn, $oid, "c");
   var_dump($attr);
}


cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
array(3) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "2"
  [2]=>
  string(1) "3"
}
[004] 
array(0) {
}
[005] 
bool(false)
[006] 
string(12) "Hello world!"
[007] 
string(9) "{7, 8, 9}"
string(12) "Hello world!"
[008] 
string(1) "8"
string(0) ""
Finished!
