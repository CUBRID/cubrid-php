--TEST--
cubrid_schema
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";

//CUBRID_SCH_ATTRIBUTE parameter
//table contains index, reverse index, unique index,shared, not null and REVERSE UNIQUE INDEX 
printf("\n#####CUBRID_SCH_CLASS_ATTRIBUTE #####\n");
$conn = cubrid_connect_with_url($connect_url, $user, $passwd);

cubrid_execute($conn,"drop table if EXISTS schema_2;");
cubrid_execute($conn,"drop table if EXISTS schema_1;");
cubrid_execute($conn,"CREATE TABLE schema_1(id INT NOT NULL DEFAULT 0 PRIMARY KEY,phone VARCHAR(10),address string,email char(30),coment string SHARED 'no things');");
cubrid_execute($conn,"CREATE TABLE schema_2(ID INT NOT NULL,name VARCHAR(10) NOT NULL,salary double,CONSTRAINT pk_id PRIMARY KEY(id), CONSTRAINT fk_id FOREIGN KEY(id) REFERENCES schema_1(id) ON DELETE CASCADE ON UPDATE RESTRICT);");
cubrid_execute($conn,"create index schema_1_index on schema_1(address)");
cubrid_execute($conn,"create reverse unique index schema_1_rever_unique on schema_1(email)");
cubrid_execute($conn,"create reverse index schema_1_reverse on schema_1(phone)");
cubrid_execute($conn,"create index schema_2_index on schema_2(id,name)");
cubrid_execute($conn,"create unique index schema_2_unique on schema_2(salary)");
cubrid_execute($conn,"insert into schema_1(id, phone, address,email) values(1,'1111-11-11','changping','kklll@ooo.oo.oo')");
cubrid_execute($conn,"insert into schema_2 values(1,'name2',10000.00)");


$schema3 = cubrid_schema($conn,CUBRID_SCH_ATTRIBUTE,"schema_1");
var_dump($schema3);

$schema4 = cubrid_schema($conn,CUBRID_SCH_ATTRIBUTE,"schema_2");
var_dump($schema4);



cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--XFAIL--
http://jira.cubrid.org/browse/APIS-68
--EXPECTF--
#####CUBRID_SCH_CLASS_ATTRIBUTE #####
array(5) {
  [0]=>
  array(13) {
    ["ATTR_NAME"]=>
    string(2) "id"
    ["DOMAIN"]=>
    string(1) "8"
    ["SCALE"]=>
    string(1) "0"
    ["PRECISION"]=>
    string(2) "0"
    ["INDEXED"]=>
    string(1) "1"
    ["NON_NULL"]=>
    string(1) "1"
    ["SHARED"]=>
    string(1) "0"
    ["UNIQUE"]=>
    string(1) "1"
    ["DEFAULT"]=>
    string(1) "0"
    ["ATTR_ORDER"]=>
    string(1) "1"
    ["CLASS_NAME"]=>
    string(8) "schema_1"
    ["SOURCE_CLASS"]=>
    string(8) "schema_1"
    ["IS_KEY"]=>
    string(1) "1"
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
    string(8) "schema_1"
    ["SOURCE_CLASS"]=>
    string(8) "schema_1"
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
    string(1) "0"
    ["DEFAULT"]=>
    NULL
    ["ATTR_ORDER"]=>
    string(1) "3"
    ["CLASS_NAME"]=>
    string(8) "schema_1"
    ["SOURCE_CLASS"]=>
    string(8) "schema_1"
    ["IS_KEY"]=>
    string(1) "0"
  }
  [3]=>
  array(13) {
    ["ATTR_NAME"]=>
    string(5) "email"
    ["DOMAIN"]=>
    string(1) "1"
    ["SCALE"]=>
    string(1) "0"
    ["PRECISION"]=>
    string(2) "30"
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
    string(1) "4"
    ["CLASS_NAME"]=>
    string(8) "schema_1"
    ["SOURCE_CLASS"]=>
    string(8) "schema_1"
    ["IS_KEY"]=>
    string(1) "0"
  }
  [4]=>
  array(13) {
    ["ATTR_NAME"]=>
    string(6) "coment"
    ["DOMAIN"]=>
    string(1) "2"
    ["SCALE"]=>
    string(1) "0"
    ["PRECISION"]=>
    string(10) "1073741823"
    ["INDEXED"]=>
    string(1) "0"
    ["NON_NULL"]=>
    string(1) "0"
    ["SHARED"]=>
    string(1) "1"
    ["UNIQUE"]=>
    string(1) "0"
    ["DEFAULT"]=>
    string(9) "no things"
    ["ATTR_ORDER"]=>
    string(1) "5"
    ["CLASS_NAME"]=>
    string(8) "schema_1"
    ["SOURCE_CLASS"]=>
    string(8) "schema_1"
    ["IS_KEY"]=>
    string(1) "0"
  }
}
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
    string(2) "10"
    ["INDEXED"]=>
    string(1) "1"
    ["NON_NULL"]=>
    string(1) "1"
    ["SHARED"]=>
    string(1) "0"
    ["UNIQUE"]=>
    string(1) "1"
    ["DEFAULT"]=>
    NULL
    ["ATTR_ORDER"]=>
    string(1) "1"
    ["CLASS_NAME"]=>
    string(8) "schema_2"
    ["SOURCE_CLASS"]=>
    string(8) "schema_2"
    ["IS_KEY"]=>
    string(1) "1"
  }
  [1]=>
  array(13) {
    ["ATTR_NAME"]=>
    string(4) "name"
    ["DOMAIN"]=>
    string(1) "2"
    ["SCALE"]=>
    string(1) "0"
    ["PRECISION"]=>
    string(2) "10"
    ["INDEXED"]=>
    string(1) "1"
    ["NON_NULL"]=>
    string(1) "1"
    ["SHARED"]=>
    string(1) "0"
    ["UNIQUE"]=>
    string(1) "0"
    ["DEFAULT"]=>
    NULL
    ["ATTR_ORDER"]=>
    string(1) "2"
    ["CLASS_NAME"]=>
    string(8) "schema_2"
    ["SOURCE_CLASS"]=>
    string(8) "schema_2"
    ["IS_KEY"]=>
    string(1) "0"
  }
  [2]=>
  array(13) {
    ["ATTR_NAME"]=>
    string(6) "salary"
    ["DOMAIN"]=>
    string(2) "12"
    ["SCALE"]=>
    string(1) "0"
    ["PRECISION"]=>
    string(2) "15"
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
    string(8) "schema_2"
    ["SOURCE_CLASS"]=>
    string(8) "schema_2"
    ["IS_KEY"]=>
    string(1) "0"
  }
}
Finished!
