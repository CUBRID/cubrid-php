--TEST--
cubrid_bind
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
cubrid_execute($conn,"drop table if EXISTS tb;");
cubrid_execute($conn,"CREATE TABLE tb(id int, phone VARCHAR(10),address string);");
cubrid_execute($conn,"create reverse unique index rever_unique_tb on tb(id)");
cubrid_execute($conn,"create reverse index reverse_tb on tb(phone)");
cubrid_execute($conn,"create unique index unique_tb on tb(address)");
cubrid_execute($conn,"insert into tb(id, phone, address) values(1,'1111-11-11','changping')");
$schema = cubrid_schema($conn,CUBRID_SCH_ATTRIBUTE,"tb");
var_dump($schema);

cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
array(3) {
  [0]=>
  array(13) {
    ["ATTR_NAME"]=>
    string(2) "id"
    ["DOMAIN"]=>
    string(1) "8"
    ["SCALE"]=>
    string(1) "0"
    ["PRECISION"]=>
    string(1) "0"
    ["INDEXED"]=>
    string(1) "1"
    ["NON_NULL"]=>
    string(1) "0"
    ["SHARED"]=>
    string(1) "0"
    ["UNIQUE"]=>
    string(1) "0"
    ["DEFAULT"]=>
    NULL
    ["ATTR_ORDER"]=>
    string(1) "1"
    ["CLASS_NAME"]=>
    string(2) "tb"
    ["SOURCE_CLASS"]=>
    string(2) "tb"
    ["IS_KEY"]=>
    string(1) "0"
  }
  [1]=>
  array(13) {
    ["ATTR_NAME"]=>
    string(5) "phone"
    ["DOMAIN"]=>
    string(1) "2"
    ["SCALE"]=>
    string(1) "0"
    ["PRECISION"]=>
    string(2) "10"
    ["INDEXED"]=>
    string(1) "1"
    ["NON_NULL"]=>
    string(1) "0"
    ["SHARED"]=>
    string(1) "0"
    ["UNIQUE"]=>
    string(1) "0"
    ["DEFAULT"]=>
    NULL
    ["ATTR_ORDER"]=>
    string(1) "2"
    ["CLASS_NAME"]=>
    string(2) "tb"
    ["SOURCE_CLASS"]=>
    string(2) "tb"
    ["IS_KEY"]=>
    string(1) "0"
  }
  [2]=>
  array(13) {
    ["ATTR_NAME"]=>
    string(7) "address"
    ["DOMAIN"]=>
    string(1) "2"
    ["SCALE"]=>
    string(1) "0"
    ["PRECISION"]=>
    string(10) "1073741823"
    ["INDEXED"]=>
    string(1) "1"
    ["NON_NULL"]=>
    string(1) "0"
    ["SHARED"]=>
    string(1) "0"
    ["UNIQUE"]=>
    string(1) "1"
    ["DEFAULT"]=>
    NULL
    ["ATTR_ORDER"]=>
    string(1) "3"
    ["CLASS_NAME"]=>
    string(2) "tb"
    ["SOURCE_CLASS"]=>
    string(2) "tb"
    ["IS_KEY"]=>
    string(1) "0"
  }
}
Finished!
