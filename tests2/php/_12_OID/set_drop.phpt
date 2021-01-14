--TEST--
cubrid_set_add cubrid_set_drop
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect_with_url($connect_url, $user, $passwd);
if (!$conn) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
    exit(1);
}
cubrid_execute($conn,"drop table if exists php_cubrid_test");
cubrid_execute($conn, "CREATE TABLE php_cubrid_test (a int AUTO_INCREMENT, b set(int), c list(int), d char(30)) DONT_REUSE_OID");
cubrid_execute($conn, "INSERT INTO php_cubrid_test(a, b, c, d) VALUES (1, {1,2,3}, {11, 22, 33, 333}, 'a')");
cubrid_execute($conn, "INSERT INTO php_cubrid_test(a, b, c, d) VALUES (2, {4,5,7}, {44, 55, 66, 666}, 'b')");
cubrid_execute($conn, "INSERT INTO php_cubrid_test(a, b, c, d) VALUES (3, {9,10,11}, {77,999,888,0000}, 'c')");

if (!$req = cubrid_execute($conn, "select * from php_cubrid_test", CUBRID_INCLUDE_OID)) {
    printf("[002] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

$oid = cubrid_current_oid($req);
if (is_null ($oid)){
    printf("[003] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}
cubrid_move_cursor($req, 1, CUBRID_CURSOR_FIRST);

printf("#####error drop#####\n");
if (!cubrid_set_drop($conn, $oid, "b", "4")) {
    printf("[004] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    $attr = cubrid_col_get($conn, $oid, "b");
    var_dump($attr);
}

if (!cubrid_set_drop($conn, $oid, "a", "1")) {
    printf("[004] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    $attr = cubrid_col_get($conn, $oid, "a");
    var_dump($attr);
}

if (!cubrid_set_drop($conn, $oid, "c", "1111")) {
    printf("[004] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    $attr = cubrid_col_get($conn, $oid, "c");
    var_dump($attr);
}

if (!cubrid_set_drop($conn, $oid, "b", "no this value")) {
    printf("[004] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    $attr = cubrid_col_get($conn, $oid, "b");
    var_dump($attr);
}

printf("#####correct drop#####\n");
$array=array(1,2,3,4);
foreach( $array as $i=>$value){
   cubrid_set_drop($conn, $oid, "b",$array[$i]);
}
$attr = cubrid_col_get($conn, $oid, "b");
var_dump($attr);

$array=array(11,22,33,333);
foreach( $array as $i=>$value){
   cubrid_set_drop($conn, $oid, "c",$array[$i]);
}
$attr = cubrid_col_get($conn, $oid, "c");
var_dump($attr);

cubrid_close_request($req);
cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####error drop#####
array(3) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "2"
  [2]=>
  string(1) "3"
}

Warning: Error: CAS, -10020, The attribute domain must be the set type in %s on line %d
[004] [-10020] The attribute domain must be the set type
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

Warning: Error: DBMS, -181, Cannot coerce value of domain "character varying" to domain "integer".%s. in %s on line %d
[004] [-181] Cannot coerce value of domain "character varying" to domain "integer".%s.
#####correct drop#####
array(0) {
}
array(4) {
  [0]=>
  NULL
  [1]=>
  NULL
  [2]=>
  NULL
  [3]=>
  NULL
}
Finished!



