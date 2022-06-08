--TEST--
cubrid_fetch_field
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

//Various types of tables

printf("#####First: Data type is numeric#####\n");
$delete_result1=cubrid_query("drop class if exists numeric_tb");
if (!$delete_result1) {
    die('Delete Failed: ' . cubrid_error());
}
$create_result1=cubrid_query("create class numeric_tb(smallint_t smallint,short_t short, int_t int ,bigint_t bigint,decimal_t decimal(15,2), numeric_t numeric(38,10), float_t float, real_t real, monetary_t monetary, double_t double )");
if (!$create_result1) {
    die('Create Failed: ' . cubrid_error());
}
cubrid_execute($conn,"insert into numeric_tb values(-32768,32767,2147483647,-9223372036854775808,0.12345678,12345.6789,-3.402823466E+38,+3.402823466E+38,-3.402823466E+38,-1.7976931348623157E+308);");

$result = cubrid_execute($conn, "SELECT smallint_t,short_t,int_t,bigint_t,decimal_t,numeric_t,float_t,real_t, monetary_t FROM numeric_tb;");
var_dump(cubrid_fetch_row($result) );

//smallint
cubrid_field_seek($result, 0);
$field = cubrid_fetch_field($result);
$type="smallint";
$index=0;
get_field_property($field,$type,$index,$result);

//short
cubrid_field_seek($result, 1);
$field = cubrid_fetch_field($result);
$type="short";
$index=1;
get_field_property($field,$type,$index,$result);

//bigint
cubrid_field_seek($result, 3);
$field = cubrid_fetch_field($result);
$type="bigint";
$index=3;
get_field_property($field,$type,$index,$result);

//decimal
cubrid_field_seek($result, 4);
$field = cubrid_fetch_field($result);
$type="decimal";
$index=4;
get_field_property($field,$type,$index,$result);

//numeric
cubrid_field_seek($result, 5);
$field = cubrid_fetch_field($result);
$type="numeric";
$index=5;
get_field_property($field,$type,$index,$result);

//float
cubrid_field_seek($result, 6);
$field = cubrid_fetch_field($result);
$type="float";
$index=6;
get_field_property($field,$type,$index,$result);

//real
cubrid_field_seek($result, 7);
$field = cubrid_fetch_field($result);
$type="real";
$index=7;
get_field_property($field,$type,$index,$result);

//monetary
cubrid_field_seek($result, 8);
$field = cubrid_fetch_field($result);
$type="monetary";
$index=8;
get_field_property($field,$type,$index,$result);

function get_field_property($field,$type,$index,$result){
   printf("\n\n---$type Field Properties ---\n");
   printf("%s %s\n", "name:", $field->name);
   printf("%s %s\n", "table:", $field->table);
   printf("%s \"%s\"\n", "default value:", $field->def);
   printf("%s %d\n", "max lenght:", $field->max_length);
   printf("%s %d\n", "not null:", $field->not_null);
   printf("%s %d\n", "primary_key:", $field->primary_key);
   printf("%s %d\n", "unique key:", $field->unique_key);
   printf("%s %d\n", "multiple key:", $field->multiple_key);
   printf("%s %d\n", "numeric:", $field->numeric);
   printf("%s %d\n", "blob:", $field->blob);
   printf("%s %s\n", "type:", $field->type);
   printf("%s %d\n", "unsigned:", $field->unsigned);
   printf("%s %d\n", "zerofill:", $field->zerofill);
   printf("cubrid_field_len: %s\n",cubrid_field_len($result,$index));
}

cubrid_close_prepare($result); 
cubrid_disconnect($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
#####First: Data type is numeric#####
array(9) {
  [0]=>
  string(6) "-32768"
  [1]=>
  string(5) "32767"
  [2]=>
  string(10) "2147483647"
  [3]=>
  string(20) "-9223372036854775808"
  [4]=>
  string(4) "0.12"
  [5]=>
  string(16) "12345.6789000000"
  [6]=>
  string(47) "-3402823466385288%d.000000"
  [7]=>
  string(46) "3402823466385288%d.000000"
  [8]=>
  string(57) "-3402823466000000%d.0000000000000000"
}


---smallint Field Properties ---
name: smallint_t
table: dba.numeric_tb
default value: "NULL"
max lenght: 0
not null: 0
primary_key: 0
unique key: 0
multiple key: 1
numeric: 1
blob: 0
type: smallint
unsigned: 0
zerofill: 0
cubrid_field_len: 6


---short Field Properties ---
name: short_t
table: dba.numeric_tb
default value: "NULL"
max lenght: 0
not null: 0
primary_key: 0
unique key: 0
multiple key: 1
numeric: 1
blob: 0
type: smallint
unsigned: 0
zerofill: 0
cubrid_field_len: 6


---bigint Field Properties ---
name: bigint_t
table: dba.numeric_tb
default value: "NULL"
max lenght: 0
not null: 0
primary_key: 0
unique key: 0
multiple key: 1
numeric: 1
blob: 0
type: bigint
unsigned: 0
zerofill: 0
cubrid_field_len: 20


---decimal Field Properties ---
name: decimal_t
table: dba.numeric_tb
default value: "NULL"
max lenght: 0
not null: 0
primary_key: 0
unique key: 0
multiple key: 1
numeric: 1
blob: 0
type: numeric
unsigned: 0
zerofill: 0
cubrid_field_len: 17


---numeric Field Properties ---
name: numeric_t
table: dba.numeric_tb
default value: "NULL"
max lenght: 0
not null: 0
primary_key: 0
unique key: 0
multiple key: 1
numeric: 1
blob: 0
type: numeric
unsigned: 0
zerofill: 0
cubrid_field_len: 40


---float Field Properties ---
name: float_t
table: dba.numeric_tb
default value: "NULL"
max lenght: 0
not null: 0
primary_key: 0
unique key: 0
multiple key: 1
numeric: 1
blob: 0
type: float
unsigned: 0
zerofill: 0
cubrid_field_len: 15


---real Field Properties ---
name: real_t
table: dba.numeric_tb
default value: "NULL"
max lenght: 0
not null: 0
primary_key: 0
unique key: 0
multiple key: 1
numeric: 1
blob: 0
type: float
unsigned: 0
zerofill: 0
cubrid_field_len: 15


---monetary Field Properties ---
name: monetary_t
table: dba.numeric_tb
default value: "NULL"
max lenght: 0
not null: 0
primary_key: 0
unique key: 0
multiple key: 1
numeric: 1
blob: 0
type: monetary
unsigned: 0
zerofill: 0
cubrid_field_len: 30
Finished!
