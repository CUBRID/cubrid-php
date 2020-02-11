--TEST--
cubrid_schema CUBRID_SCH_CONSTRAINT
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";

//CUBRID_SCH_CONSTRAINT parameter
//table contains index, reverse index, unique index,shared, not null and REVERSE UNIQUE INDEX 
printf("\n#####CUBRID_SCH_CONSTRAINT #####\n");
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

printf("\n#####positive example#####\n");
$schema1 = cubrid_schema($conn,CUBRID_SCH_CONSTRAINT,"schema_1");
var_dump($schema1);

$schema2 = cubrid_schema($conn,CUBRID_SCH_CONSTRAINT,"schema_2");
var_dump($schema2);

printf("\n#####negative example#####\n");
$schema5 = cubrid_schema($conn,CUBRID_SCH_CONSTRAINT);
if(FALSE == $schema5){
    printf("[005] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value5: \n");
    var_dump($schema5);
}

$schema6 = cubrid_schema($conn,CUBRID_SCH_CONSTRAINT,"nothisparameter");
if(FALSE == $schema6){
    printf("[006] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value6: \n");
    var_dump($schema6);
}  


cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####CUBRID_SCH_CONSTRAINT #####

#####positive example#####
array(3) {
  [0]=>
  array(8) {
    ["TYPE"]=>
    string(1) "0"
    ["NAME"]=>
    string(21) "schema_1_rever_unique"
    ["ATTR_NAME"]=>
    string(5) "email"
    ["NUM_PAGES"]=>
    string(1) "0"
    ["NUM_KEYS"]=>
    string(1) "0"
    ["PRIMARY_KEY"]=>
    string(1) "0"
    ["KEY_ORDER"]=>
    string(1) "1"
    ["ASC_DESC"]=>
    string(1) "D"
  }
  [1]=>
  array(8) {
    ["TYPE"]=>
    string(1) "1"
    ["NAME"]=>
    string(14) "schema_1_index"
    ["ATTR_NAME"]=>
    string(7) "address"
    ["NUM_PAGES"]=>
    string(1) "0"
    ["NUM_KEYS"]=>
    string(1) "0"
    ["PRIMARY_KEY"]=>
    string(1) "0"
    ["KEY_ORDER"]=>
    string(1) "1"
    ["ASC_DESC"]=>
    string(1) "A"
  }
  [2]=>
  array(8) {
    ["TYPE"]=>
    string(1) "1"
    ["NAME"]=>
    string(16) "schema_1_reverse"
    ["ATTR_NAME"]=>
    string(5) "phone"
    ["NUM_PAGES"]=>
    string(1) "0"
    ["NUM_KEYS"]=>
    string(1) "0"
    ["PRIMARY_KEY"]=>
    string(1) "0"
    ["KEY_ORDER"]=>
    string(1) "1"
    ["ASC_DESC"]=>
    string(1) "D"
  }
}
array(3) {
  [0]=>
  array(8) {
    ["TYPE"]=>
    string(1) "0"
    ["NAME"]=>
    string(15) "schema_2_unique"
    ["ATTR_NAME"]=>
    string(6) "salary"
    ["NUM_PAGES"]=>
    string(1) "0"
    ["NUM_KEYS"]=>
    string(1) "0"
    ["PRIMARY_KEY"]=>
    string(1) "0"
    ["KEY_ORDER"]=>
    string(1) "1"
    ["ASC_DESC"]=>
    string(1) "A"
  }
  [1]=>
  array(8) {
    ["TYPE"]=>
    string(1) "1"
    ["NAME"]=>
    string(14) "schema_2_index"
    ["ATTR_NAME"]=>
    string(2) "id"
    ["NUM_PAGES"]=>
    string(1) "0"
    ["NUM_KEYS"]=>
    string(1) "0"
    ["PRIMARY_KEY"]=>
    string(1) "0"
    ["KEY_ORDER"]=>
    string(1) "1"
    ["ASC_DESC"]=>
    string(1) "A"
  }
  [2]=>
  array(8) {
    ["TYPE"]=>
    string(1) "1"
    ["NAME"]=>
    string(14) "schema_2_index"
    ["ATTR_NAME"]=>
    string(4) "name"
    ["NUM_PAGES"]=>
    string(1) "0"
    ["NUM_KEYS"]=>
    string(1) "0"
    ["PRIMARY_KEY"]=>
    string(1) "0"
    ["KEY_ORDER"]=>
    string(1) "2"
    ["ASC_DESC"]=>
    string(1) "A"
  }
}

#####negative example#####
[005] Expecting false, got [0] []
[006] Expecting false, got [0] []
Finished!
