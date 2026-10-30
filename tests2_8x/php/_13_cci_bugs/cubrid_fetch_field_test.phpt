--TEST--
cubrid_bind
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');
$conn = cubrid_connect($host, $port,"demodb", $user, $passwd);

$req = cubrid_execute($conn, "SELECT event_code,athlete_code,nation_code,game_date FROM public.game WHERE host_year=1988 and event_code=20001 order by athlete_code desc;");

var_dump(cubrid_fetch_row($req));

cubrid_field_seek($req, 1);
$field = cubrid_fetch_field($req);

printf("\n--- Field Properties ---\n");
printf("%-30s %s\n", "name:", $field->name);
printf("%-30s %s\n", "table:", $field->table);
printf("%-30s \"%s\"\n", "default value:", $field->def);
printf("%-30s %d\n", "max_length:", $field->max_length);
printf("%-30s %d\n", "not null:", $field->not_null);
printf("%-30s %d\n", "primary key:", $field->primary_key);
printf("%-30s %d\n", "unique key:", $field->unique_key);
printf("%-30s %d\n", "multiple key:", $field->multiple_key);
printf("%-30s %d\n", "numeric:", $field->numeric);
printf("%-30s %d\n", "blob:", $field->blob);
printf("%-30s %s\n", "type:", $field->type);
printf("%-30s %d\n", "unsigned:", $field->unsigned);
printf("%-30s %d\n", "zerofill:", $field->zerofill);
printf("cubrid_field_len: %s\n",cubrid_field_len($req,1));

cubrid_close_request($req);

cubrid_disconnect($conn);
?>
--CLEAN--
--EXPECTF--
array(4) {
  [0]=>
  string(5) "20001"
  [1]=>
  string(5) "16681"
  [2]=>
  string(3) "KOR"
  [3]=>
  string(10) "1988-09-30"
}

--- Field Properties ---
name:                          athlete_code
table:                         public.game
default value:                 "NULL"
max_length:                    0
not null:                      1
primary key:                   1
unique key:                    1
multiple key:                  0
numeric:                       1
blob:                          0
type:                          integer
unsigned:                      0
zerofill:                      0
cubrid_field_len: 11
