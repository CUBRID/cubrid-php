--TEST--
column
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

//Data type is character strings
$delete_result2=cubrid_query("drop class if exists character_tb");
if (!$delete_result2) {
    die('Delete Failed: ' . cubrid_error());
}
$create_result2=cubrid_query("create class character_tb(char_t char(5), varchar_t varchar(11), nchar_t nchar(20), ncharvarying_t nchar varying(536870911))");
if (!$create_result2) {
    die('Create Failed: ' . cubrid_error());
}
cubrid_execute($conn,"insert into character_tb values('char1','varchar1',N'aaa',N'bbb')");
$result = cubrid_execute($conn, "SELECT * FROM character_tb;");
var_dump(cubrid_fetch_row($result) );

printf("#####Data type is character strings#####\n");
cubrid_field_seek($result, 0);
$field = cubrid_fetch_field($result);

printf("\n---char Field Properties ---\n");
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
printf("cubrid_field_len: %s\n",cubrid_field_len($result,0));


cubrid_field_seek($result, 1);
$field = cubrid_fetch_field($result);
printf("\n---varchar Field Properties ---\n");
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
printf("cubrid_field_len: %s\n",cubrid_field_len($result,1));


cubrid_field_seek($result, 2);
$field = cubrid_fetch_field($result);
printf("\n---nchar Field Properties ---\n");
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
printf("cubrid_field_len: %s\n",cubrid_field_len($result,2));

cubrid_field_seek($result, 3);
$field = cubrid_fetch_field($result);
printf("\n---nchar varying Field Properties ---\n");
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
printf("cubrid_field_len: %s\n",cubrid_field_len($result,3));


cubrid_disconnect($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
array(4) {
  [0]=>
  string(5) "char1"
  [1]=>
  string(8) "varchar1"
  [2]=>
  string(20) "aaa                 "
  [3]=>
  string(3) "bbb"
}
#####Data type is character strings#####

---char Field Properties ---
name: char_t
table: dba.character_tb
default value: "NULL"
max lenght: 0
not null: 0
primary_key: 0
unique key: 0
multiple key: 1
numeric: 0
blob: 0
type: char
unsigned: 0
zerofill: 0
cubrid_field_len: 5

---varchar Field Properties ---
name: varchar_t
table: dba.character_tb
default value: "NULL"
max lenght: 0
not null: 0
primary_key: 0
unique key: 0
multiple key: 1
numeric: 0
blob: 0
type: varchar
unsigned: 0
zerofill: 0
cubrid_field_len: 11

---nchar Field Properties ---
name: nchar_t
table: dba.character_tb
default value: "NULL"
max lenght: 0
not null: 0
primary_key: 0
unique key: 0
multiple key: 1
numeric: 0
blob: 0
type: nchar
unsigned: 0
zerofill: 0
cubrid_field_len: 20

---nchar varying Field Properties ---
name: ncharvarying_t
table: dba.character_tb
default value: "NULL"
max lenght: 0
not null: 0
primary_key: 0
unique key: 0
multiple key: 1
numeric: 0
blob: 0
type: varnchar
unsigned: 0
zerofill: 0
cubrid_field_len: 536870911
Finished!
