--TEST--
cubrid_fetch_field
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
//table has primary key or foreign key
include_once("connect.inc");
$conn = cubrid_connect_with_url($connect_url, $user, $passwd);
cubrid_execute($conn,"drop table if exists fetch_tb;");
cubrid_execute($conn,"CREATE TABLE fetch_tb(c1 string primary key, c2 char(20) not null, c3 int default -2147483648 unique key, c4 double default 22.22, c5 time default TIME '23:59:59', c6 date, c7 TIMESTAMP default TIMESTAMP  '2038-01-19 12:14:07',c8 bit, c9 numeric(13,4),c10 clob,c11 blob);");
cubrid_execute($conn,"insert into fetch_tb values('string111111','char11111',1,11.11,TIME '02:10:00',DATE '1977-08-14', TIMESTAMP '1977-08-14 5:35:00 pm',B'1',432341.4321, CHAR_TO_CLOB('This is a Dog'), BIT_TO_BLOB(X'000001'))");

print("#####positive example#####\n");
$result=cubrid_execute($conn,"select c1,c2,c3,c4,c5,c6,c7,c8,c9,CLOB_TO_CHAR(c10) as c10, BLOB_TO_BIT(c11) from fetch_tb");
var_dump(cubrid_fetch_row($result) );

//string
cubrid_field_seek($result, 0);
$field = cubrid_fetch_field($result);
$type="string";
$index=0;
get_field_property($field,$type,$index,$result);

//char
$type="char";
$index=1;
cubrid_field_seek($result, 1);
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//int
$type="int";
$index=2;
cubrid_field_seek($result, 2);
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//double
$type="double";
$index=3;
cubrid_field_seek($result, 3);
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//time
$type="time";
$index=4;
cubrid_field_seek($result, 4);
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//date
$type="date";
$index=5;
cubrid_field_seek($result, 5);
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//timestamp
$type="timestamp";
$index=6;
cubrid_field_seek($result, 6);
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//bit
$type="bit";
$index=7;
cubrid_field_seek($result, 7);
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//numeric
$type="numeric";
$index=8;
cubrid_field_seek($result,8);
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//clob
$type="clob";
$index=9;
cubrid_field_seek($result, 9);
$field = cubrid_fetch_field($result);
get_field_property($field,$type,$index,$result);

//blob
$type="blob";
$index=10;
cubrid_field_seek($result, 10);
$field = cubrid_fetch_field($result);
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

cubrid_close_request($result);
cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--XFAIL--
http://jira.cubrid.org/browse/APIS-358
--EXPECTF--
#####positive example#####
array(11) {
  [0]=>
  string(12) "string111111"
  [1]=>
  string(20) "char11111           "
  [2]=>
  string(1) "1"
  [3]=>
  string(19) "11.1099999999999994"
  [4]=>
  string(8) "02:10:00"
  [5]=>
  string(10) "1977-08-14"
  [6]=>
  string(19) "1977-08-14 17:35:00"
  [7]=>
  string(2) "80"
  [8]=>
  string(11) "432341.4321"
  [9]=>
  string(13) "This is a Dog"
  [10]=>
  string(6) "000001"
}


---string Field Properties ---
name: c1
table: fetch_tb
default value: ""
max lenght: 0
not null: 1
primary_key: 1
unique key: 1
multiple key: 0
numeric: 0
blob: 0
type: varchar
unsigned: 0
zerofill: 0
cubrid_field_len: 1073741823


---char Field Properties ---
name: c2
table: fetch_tb
default value: ""
max lenght: 0
not null: 1
primary_key: 0
unique key: 0
multiple key: 1
numeric: 0
blob: 0
type: char
unsigned: 0
zerofill: 0
cubrid_field_len: 20


---int Field Properties ---
name: c3
table: fetch_tb
default value: "-2147483648"
max lenght: 0
not null: 0
primary_key: 0
unique key: 1
multiple key: 0
numeric: 1
blob: 0
type: integer
unsigned: 0
zerofill: 0
cubrid_field_len: 11


---double Field Properties ---
name: c4
table: fetch_tb
default value: "22.22"
max lenght: 0
not null: 0
primary_key: 0
unique key: 0
multiple key: 1
numeric: 1
blob: 0
type: double
unsigned: 0
zerofill: 0
cubrid_field_len: 29


---time Field Properties ---
name: c5
table: fetch_tb
default value: "11:59:59 PM"
max lenght: 0
not null: 0
primary_key: 0
unique key: 0
multiple key: 1
numeric: 0
blob: 0
type: time
unsigned: 0
zerofill: 0
cubrid_field_len: 8


---date Field Properties ---
name: c6
table: fetch_tb
default value: ""
max lenght: 0
not null: 0
primary_key: 0
unique key: 0
multiple key: 1
numeric: 0
blob: 0
type: date
unsigned: 0
zerofill: 0
cubrid_field_len: 10


---timestamp Field Properties ---
name: c7
table: fetch_tb
default value: "12:14:07 PM 01/19/2038"
max lenght: 0
not null: 0
primary_key: 0
unique key: 0
multiple key: 1
numeric: 0
blob: 0
type: timestamp
unsigned: 0
zerofill: 0
cubrid_field_len: 23


---bit Field Properties ---
name: c8
table: fetch_tb
default value: ""
max lenght: 0
not null: 0
primary_key: 0
unique key: 0
multiple key: 1
numeric: 0
blob: 0
type: bit
unsigned: 0
zerofill: 0
cubrid_field_len: 1


---numeric Field Properties ---
name: c9
table: fetch_tb
default value: ""
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
cubrid_field_len: 15


---clob Field Properties ---
name: c10
table: 
default value: ""
max lenght: 0
not null: 1
primary_key: 0
unique key: 0
multiple key: 1
numeric: 0
blob: 0
type: varchar
unsigned: 0
zerofill: 0
cubrid_field_len: 1073741823


---blob Field Properties ---
name: blob_to_bit(c11)
table: 
default value: ""
max lenght: 0
not null: 1
primary_key: 0
unique key: 0
multiple key: 1
numeric: 0
blob: 0
type: varbit
unsigned: 0
zerofill: 0
cubrid_field_len: 1073741823
Finished!
