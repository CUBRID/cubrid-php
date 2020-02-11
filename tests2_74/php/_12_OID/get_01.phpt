--TEST--
cubrid_get
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php

include_once("connect.inc");

$conn = cubrid_connect_with_url($connect_url);
if (!$conn) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
    exit(1);
}

cubrid_query("DROP TABLE IF EXISTS get_01", $conn);
cubrid_execute($conn, "CREATE TABLE get_01(a int AUTO_INCREMENT, b set(int), c list(int), d char(30))");
cubrid_execute($conn, "INSERT INTO get_01(a, b, c, d) VALUES (1, {1,2,3}, {11, 22, 33, 333}, 'a')");
if (!$req = cubrid_execute($conn, "select * from get_01", CUBRID_INCLUDE_OID)) {
    printf("[002] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}
$oid = cubrid_current_oid($req);
if (is_null ($oid)){
    printf("[003] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

printf("#####correct get#####\n");
cubrid_move_cursor($req, 1, CUBRID_CURSOR_FIRST);
$attr = cubrid_get($conn, $oid, "d");
var_dump($attr);

$attr = cubrid_get($conn, $oid, "b");
var_dump($attr);

$attr = cubrid_get($conn, $oid);
var_dump($attr);

$array=array("a","c");
$attr = cubrid_get($conn, $oid,$array);
var_dump($attr);
printf("\n\n");
cubrid_close_prepare($req);

printf("#####error get#####\n");
$req1 = cubrid_execute($conn, "select * from get_01", CUBRID_INCLUDE_OID);
cubrid_move_cursor($req1, 1, CUBRID_CURSOR_FIRST);
$oid = cubrid_current_oid($req1);

//not this arrtr
$attr = cubrid_get($conn, $oid, "nothisstring");
if (is_null ($attr)){
    printf("[004]NULL [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}elseif(FALSE == $attr){
    printf("[004]FALSE [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    var_dump($attr);
}


//not this oid
$attr = cubrid_get($conn,"not a oid");
if (is_null ($attr)){
    printf("[005]NULL [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}elseif(FALSE == $attr){
    printf("[005]FALSE [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    var_dump($attr);
}

//empty array
$empty_array=array();
$attr_empty = cubrid_get($conn,$oid,$empty_array);
if (is_null ($attr_empty)){
    printf("[006]NULL [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}elseif(FALSE == $attr_empty ){
    printf("[006]FALSE [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    var_dump($attr_empty);
}

$nothisvalue_array=array("aa","");
$attr_nothisvalue = cubrid_get($conn,$oid,$nothisvalue_array);
if (is_null ($attr_nothisvalue)){
    printf("[007]NULL [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}elseif(FALSE == $attr_nothisvalue){
    printf("[007]FALSE [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    var_dump($attr_nothisvalue);
}

cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####correct get#####
string(30) "a                             "
string(9) "{1, 2, 3}"
array(4) {
  ["a"]=>
  string(1) "1"
  ["b"]=>
  array(3) {
    [0]=>
    string(1) "1"
    [1]=>
    string(1) "2"
    [2]=>
    string(1) "3"
  }
  ["c"]=>
  array(4) {
    [0]=>
    string(2) "11"
    [1]=>
    string(2) "22"
    [2]=>
    string(2) "33"
    [3]=>
    string(3) "333"
  }
  ["d"]=>
  string(30) "a                             "
}
array(2) {
  ["a"]=>
  string(1) "1"
  ["c"]=>
  array(4) {
    [0]=>
    string(2) "11"
    [1]=>
    string(2) "22"
    [2]=>
    string(2) "33"
    [3]=>
    string(3) "333"
  }
}


#####error get#####

Warning: Error: DBMS, -202, Attribute "nothisstring" was not found.%s in %s on line %d
[004]FALSE [-202] [Attribute "nothisstring" was not found.%s]

Warning: Error: CCI, -20020, Invalid oid string in %s on line %d
[005]FALSE [-20020] [Invalid oid string]
array(4) {
  ["a"]=>
  string(1) "1"
  ["b"]=>
  array(3) {
    [0]=>
    string(1) "1"
    [1]=>
    string(1) "2"
    [2]=>
    string(1) "3"
  }
  ["c"]=>
  array(4) {
    [0]=>
    string(2) "11"
    [1]=>
    string(2) "22"
    [2]=>
    string(2) "33"
    [3]=>
    string(3) "333"
  }
  ["d"]=>
  string(30) "a                             "
}

Warning: Error: CAS, -10013, Invalid oid in %s on line %d
[007]FALSE [-10013] [Invalid oid]
Finished!
