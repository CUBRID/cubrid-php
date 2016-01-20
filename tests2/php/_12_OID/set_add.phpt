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
cubrid_execute($conn, "CREATE TABLE php_cubrid_test (a int AUTO_INCREMENT, b set(int), c list(int), d char(30))");
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

printf("#####correct add#####\n");
$attr = cubrid_col_get($conn, $oid, "b");
var_dump($attr);
if (!cubrid_set_add($conn, $oid, "b", "4")) {
    printf("[004] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}
if (!cubrid_set_add($conn, $oid, "b", "4")) {
    printf("[005] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}
if (!cubrid_set_add($conn, $oid, "b", "4")) {
    printf("[006] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

$attr = cubrid_col_get($conn, $oid, "b");
var_dump($attr);

$attr = cubrid_col_get($conn, $oid, "c");
var_dump($attr);
if (!cubrid_set_add($conn, $oid, "c", "123345566")) {
    printf("[007] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}
$attr = cubrid_col_get($conn, $oid, "c");
var_dump($attr);


printf("#####error add#####\n");
if (!cubrid_set_add($conn, $oid, "b", "no a int type")) {
    printf("[008] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    $attr = cubrid_col_get($conn, $oid, "b");
    var_dump($attr);
}

if (!cubrid_set_add($conn, $oid, "a", "111")) {
    printf("[009] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    $attr = cubrid_col_get($conn, $oid, "b");
    var_dump($attr);
}

cubrid_close_request($req);
cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####correct add#####
array(3) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "2"
  [2]=>
  string(1) "3"
}
array(4) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "2"
  [2]=>
  string(1) "3"
  [3]=>
  string(1) "4"
}
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
array(5) {
  [0]=>
  string(2) "11"
  [1]=>
  string(2) "22"
  [2]=>
  string(2) "33"
  [3]=>
  string(3) "333"
  [4]=>
  string(9) "123345566"
}
#####error add#####

Warning: Error: DBMS, -179, Domain "character varying" is not compatible with domain "integer".%s in %s on line %d
[008] [-179] Domain "character varying" is not compatible with domain "integer".%s

Warning: Error: CAS, -1020, The attribute domain must be the set type in %s on line %d
[009] [-1020] The attribute domain must be the set type
Finished!


