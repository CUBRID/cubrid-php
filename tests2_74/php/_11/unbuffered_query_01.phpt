--TEST--
cubrid_unbuffered_query cubrid_fress_result
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");

$tmp = NULL;
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
cubrid_execute($conn, 'DROP TABLE IF EXISTS unbuffered_tb');
cubrid_execute($conn,"CREATE TABLE unbuffered_tb(id int primary key, name varchar(10))");
cubrid_execute($conn,"insert into unbuffered_tb values(1,'name1'),(2,'name2'),(3,'name3'),(4,'name4'),(5,'name5')");

printf("#####positive example#####\n");
//select statement
$unbuff=cubrid_unbuffered_query("select * from unbuffered_tb",$conn);
$result=cubrid_fetch_assoc($unbuff);
var_dump($result);
cubrid_free_result($unbuff);

$unbuff2=cubrid_unbuffered_query("select * from unbuffered_tb where id >3");
$result2=cubrid_fetch_assoc($unbuff2);
var_dump($result2);
cubrid_free_result($unbuff2);

//show statement
cubrid_execute($conn, "drop table if EXISTS unbuffer2");
cubrid_execute($conn, "CREATE TABLE unbuffer2(id INT, phone VARCHAR(10),address string,email char(30),coment string );");
cubrid_execute($conn, "create index index1 on unbuffer2(id)");
cubrid_execute($conn, "create reverse unique index reverse_unique_index on unbuffer2(phone)");
cubrid_execute($conn, "create reverse index reverse_index on unbuffer2(address)");
cubrid_execute($conn, "create unique index unique_index on unbuffer2(email)");
//cubrid_execute($conn, "");
//cubrid_execute($conn, "");
$unbuff3=cubrid_unbuffered_query("show index in unbuffer2;");
$result3=cubrid_fetch_assoc($unbuff3);
cubrid_free_result($unbuff3);

//describe
$unbuff4=cubrid_unbuffered_query("describe unbuffer2;");
if (FALSE == $unbuff4) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    $row=cubrid_fetch_assoc($unbuff4);
    var_dump($row);
}
cubrid_free_result($unbuff4);

$unbuff5=cubrid_unbuffered_query("explain unbuffered_tb;");
$result5=cubrid_fetch_assoc($unbuff5);
var_dump($result5);
cubrid_free_result($unbuff5);

$unbuff6=cubrid_unbuffered_query("insert into unbuffered_tb values(7,'name7');",$conn);
if (FALSE == $unbuff6) {
    printf("[002]No expect false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    printf("[002]Insert success. [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

$unbuff7=cubrid_unbuffered_query("delete from unbuffered_tb where id =1");
if (FALSE == $unbuff7) {
    printf("[003]No expect false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    printf("[003]Delete success. [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

$unbuff8=cubrid_unbuffered_query("drop table if exists unbuffered_tb");
if (FALSE == $unbuff8) {
    printf("[004]No expect false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    printf("[004]Drop success. [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

cubrid_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
array(2) {
  ["id"]=>
  string(1) "1"
  ["name"]=>
  string(5) "name1"
}
array(2) {
  ["id"]=>
  string(1) "4"
  ["name"]=>
  string(5) "name4"
}
array(6) {
  ["Field"]=>
  string(2) "id"
  ["Type"]=>
  string(7) "INTEGER"
  ["Null"]=>
  string(3) "YES"
  ["Key"]=>
  string(3) "MUL"
  ["Default"]=>
  NULL
  ["Extra"]=>
  string(0) ""
}
array(6) {
  ["Field"]=>
  string(2) "id"
  ["Type"]=>
  string(7) "INTEGER"
  ["Null"]=>
  string(2) "NO"
  ["Key"]=>
  string(3) "PRI"
  ["Default"]=>
  NULL
  ["Extra"]=>
  string(0) ""
}
[002]Insert success. [0] []
[003]Delete success. [0] []
[004]Drop success. [0] []
Finished!
