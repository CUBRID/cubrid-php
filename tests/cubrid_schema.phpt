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

$tmp = NULL;
$conn = NULL;

if (!is_null($tmp = @cubrid_schema())) {
    printf("[001] Expecting NULL, got %s/%s\n", gettype($tmp), $tmp);
}

if (!is_null($tmp = @cubrid_schema($conn))) {
    printf("[002] Expecting NULL, got %s/%s\n", gettype($tmp), $tmp);
}

/* =============================================== */
/* Code for testing CUBRID_SCH_ATTR_WITH_SYNONYM   */
if (!$conn = cubrid_connect($host, $port, $db,  "dba", "")) {
    printf("[003] Cannot connect to db server using host=%s, port=%d, dbname=%s, user=%s, passwd=***\n",
    $host, $port, $db, "dba");
}


cubrid_execute($conn,"CREATE USER u1;");
cubrid_execute($conn,"CREATE TABLE u1.t1(col1 int, col2 varchar(10), col3 double);");
cubrid_execute($conn,"Grant SELECT ON u1.t1 TO public;");
cubrid_execute($conn,"CREATE synonym public.s1 for u1.t1;");

cubrid_disconnect($conn);
/* =============================================== */

if (!$conn = cubrid_connect($host, $port, $db,  $user, $passwd)) {
    printf("[003] Cannot connect to db server using host=%s, port=%d, dbname=%s, user=%s, passwd=***\n",
    $host, $port, $db, $user);
}

if (($schema = cubrid_schema($conn, 1000)) !== false) {
    printf("[004] Expecting false, got %s/%s\n", gettype($schema), $schema);
}

if (($schema = cubrid_schema($conn, CUBRID_SCH_PRIMARY_KEY, "game")) === false) {
    printf("[005] Cannot get schema type CUBRID_SCH_PRIMARY_KEY when table name is \"game\"\n");
}
var_dump($schema);

if (($schema = cubrid_schema($conn, CUBRID_SCH_IMPORTED_KEYS, "game")) === false) {
    printf("[006] Cannot get schema type CUBRID_SCH_IMPORTED_KEYS when table name is \"game\"\n");
}
var_dump($schema);

if (($schema = cubrid_schema($conn, CUBRID_SCH_EXPORTED_KEYS, "event")) === false) {
    printf("[007] Cannot get schema type CUBRID_SCH_EXPORTED_KEYS when table name is \"event\", error: [%d]:%s\n", 
            cubrid_error_code(), cubrid_error_msg());
}
var_dump($schema);

if (($schema = cubrid_schema($conn, CUBRID_SCH_CROSS_REFERENCE, "event", "game")) === false) {
    printf("[008] Cannot get schema type CUBRID_SCH_EXPORTED_KEYS when table name is \"event\", error: [%d]:%s\n", 
            cubrid_error_code(), cubrid_error_msg());
}
var_dump($schema);

if (($schema = cubrid_schema($conn, CUBRID_SCH_ATTR_WITH_SYNONYM, "s1", "col1")) === false) {
    printf("[008] Cannot get schema type CUBRID_SCH_EXPORTED_KEYS when table name is \"event\", error: [%d]:%s\n", 
            cubrid_error_code(), cubrid_error_msg());
}
var_dump($schema);

if (($schema = cubrid_schema($conn, CUBRID_SCH_ATTR_WITH_SYNONYM, "public.s1", "col1")) === false) {
    printf("[008] Cannot get schema type CUBRID_SCH_EXPORTED_KEYS when table name is \"event\", error: [%d]:%s\n", 
            cubrid_error_code(), cubrid_error_msg());
}
var_dump($schema);

if (($schema = cubrid_schema($conn, CUBRID_SCH_ATTR_WITH_SYNONYM, "public.s1", "col%")) === false) {
    printf("[008] Cannot get schema type CUBRID_SCH_EXPORTED_KEYS when table name is \"event\", error: [%d]:%s\n", 
            cubrid_error_code(), cubrid_error_msg());
}
var_dump($schema);

if (($schema = cubrid_schema($conn, CUBRID_SCH_ATTR_WITH_SYNONYM, "public.s%", "col%")) === false) {
    printf("[008] Cannot get schema type CUBRID_SCH_EXPORTED_KEYS when table name is \"event\", error: [%d]:%s\n", 
            cubrid_error_code(), cubrid_error_msg());
}
var_dump($schema);

/* =============================================== */
/* Code for testing CUBRID_SCH_ATTR_WITH_SYNONYM   */
if (!$conn = cubrid_connect($host, $port, $db,  "dba", "")) {
    printf("[003] Cannot connect to db server using host=%s, port=%d, dbname=%s, user=%s, passwd=***\n",
    $host, $port, $db, "dba");
}

cubrid_execute($conn,"drop synonym if exists public.s1;");
cubrid_execute($conn,"drop table if EXISTS u1.t1;");
cubrid_execute($conn,"DROP USER u1;");

cubrid_disconnect($conn);
/* =============================================== */

print "done!";
?>
--CLEAN--
--EXPECTF--

Warning: Error: CAS, -10015, Invalid T_CCI_SCH_TYPE value in %s on line %d
array(3) {
  [0]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(11) "public.game"
    ["ATTR_NAME"]=>
    string(12) "athlete_code"
    ["KEY_SEQ"]=>
    string(1) "3"
    ["KEY_NAME"]=>
    string(41) "pk_game_host_year_event_code_athlete_code"
  }
  [1]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(11) "public.game"
    ["ATTR_NAME"]=>
    string(10) "event_code"
    ["KEY_SEQ"]=>
    string(1) "2"
    ["KEY_NAME"]=>
    string(41) "pk_game_host_year_event_code_athlete_code"
  }
  [2]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(11) "public.game"
    ["ATTR_NAME"]=>
    string(9) "host_year"
    ["KEY_SEQ"]=>
    string(1) "1"
    ["KEY_NAME"]=>
    string(41) "pk_game_host_year_event_code_athlete_code"
  }
}
array(2) {
  [0]=>
  array(9) {
    ["PKTABLE_NAME"]=>
    string(14) "public.athlete"
    ["PKCOLUMN_NAME"]=>
    string(4) "code"
    ["FKTABLE_NAME"]=>
    string(4) "game"
    ["FKCOLUMN_NAME"]=>
    string(12) "athlete_code"
    ["KEY_SEQ"]=>
    string(1) "1"
    ["UPDATE_RULE"]=>
    string(1) "1"
    ["DELETE_RULE"]=>
    string(1) "1"
    ["FK_NAME"]=>
    string(20) "fk_game_athlete_code"
    ["PK_NAME"]=>
    string(15) "pk_athlete_code"
  }
  [1]=>
  array(9) {
    ["PKTABLE_NAME"]=>
    string(12) "public.event"
    ["PKCOLUMN_NAME"]=>
    string(4) "code"
    ["FKTABLE_NAME"]=>
    string(4) "game"
    ["FKCOLUMN_NAME"]=>
    string(10) "event_code"
    ["KEY_SEQ"]=>
    string(1) "1"
    ["UPDATE_RULE"]=>
    string(1) "1"
    ["DELETE_RULE"]=>
    string(1) "1"
    ["FK_NAME"]=>
    string(18) "fk_game_event_code"
    ["PK_NAME"]=>
    string(13) "pk_event_code"
  }
}
array(1) {
  [0]=>
  array(9) {
    ["PKTABLE_NAME"]=>
    string(5) "event"
    ["PKCOLUMN_NAME"]=>
    string(4) "code"
    ["FKTABLE_NAME"]=>
    string(11) "public.game"
    ["FKCOLUMN_NAME"]=>
    string(10) "event_code"
    ["KEY_SEQ"]=>
    string(1) "1"
    ["UPDATE_RULE"]=>
    string(1) "1"
    ["DELETE_RULE"]=>
    string(1) "1"
    ["FK_NAME"]=>
    string(18) "fk_game_event_code"
    ["PK_NAME"]=>
    string(13) "pk_event_code"
  }
}
array(1) {
  [0]=>
  array(9) {
    ["PKTABLE_NAME"]=>
    string(5) "event"
    ["PKCOLUMN_NAME"]=>
    string(4) "code"
    ["FKTABLE_NAME"]=>
    string(4) "game"
    ["FKCOLUMN_NAME"]=>
    string(10) "event_code"
    ["KEY_SEQ"]=>
    string(1) "1"
    ["UPDATE_RULE"]=>
    string(1) "1"
    ["DELETE_RULE"]=>
    string(1) "1"
    ["FK_NAME"]=>
    string(18) "fk_game_event_code"
    ["PK_NAME"]=>
    string(13) "pk_event_code"
  }
}
array(1) {
  [0]=>
  array(14) {
    ["ATTR_NAME"]=>
    string(4) "col1"
    ["DOMAIN"]=>
    string(1) "8"
    ["SCALE"]=>
    string(1) "0"
    ["PRECISION"]=>
    string(2) "10"
    ["INDEXED"]=>
    string(1) "0"
    ["NON_NULL"]=>
    string(1) "0"
    ["SHARED"]=>
    string(1) "0"
    ["UNIQUE"]=>
    string(1) "0"
    ["DEFAULT"]=>
    string(4) "NULL"
    ["ATTR_ORDER"]=>
    string(1) "1"
    ["CLASS_NAME"]=>
    string(5) "u1.t1"
    ["SOURCE_CLASS"]=>
    string(5) "u1.t1"
    ["IS_KEY"]=>
    string(1) "0"
    ["REMARKS"]=>
    string(0) ""
  }
}
array(1) {
  [0]=>
  array(14) {
    ["ATTR_NAME"]=>
    string(4) "col1"
    ["DOMAIN"]=>
    string(1) "8"
    ["SCALE"]=>
    string(1) "0"
    ["PRECISION"]=>
    string(2) "10"
    ["INDEXED"]=>
    string(1) "0"
    ["NON_NULL"]=>
    string(1) "0"
    ["SHARED"]=>
    string(1) "0"
    ["UNIQUE"]=>
    string(1) "0"
    ["DEFAULT"]=>
    string(4) "NULL"
    ["ATTR_ORDER"]=>
    string(1) "1"
    ["CLASS_NAME"]=>
    string(5) "u1.t1"
    ["SOURCE_CLASS"]=>
    string(5) "u1.t1"
    ["IS_KEY"]=>
    string(1) "0"
    ["REMARKS"]=>
    string(0) ""
  }
}
array(3) {
  [0]=>
  array(14) {
    ["ATTR_NAME"]=>
    string(4) "col1"
    ["DOMAIN"]=>
    string(1) "8"
    ["SCALE"]=>
    string(1) "0"
    ["PRECISION"]=>
    string(2) "10"
    ["INDEXED"]=>
    string(1) "0"
    ["NON_NULL"]=>
    string(1) "0"
    ["SHARED"]=>
    string(1) "0"
    ["UNIQUE"]=>
    string(1) "0"
    ["DEFAULT"]=>
    string(4) "NULL"
    ["ATTR_ORDER"]=>
    string(1) "1"
    ["CLASS_NAME"]=>
    string(5) "u1.t1"
    ["SOURCE_CLASS"]=>
    string(5) "u1.t1"
    ["IS_KEY"]=>
    string(1) "0"
    ["REMARKS"]=>
    string(0) ""
  }
  [1]=>
  array(14) {
    ["ATTR_NAME"]=>
    string(4) "col2"
    ["DOMAIN"]=>
    string(1) "2"
    ["SCALE"]=>
    string(1) "0"
    ["PRECISION"]=>
    string(2) "10"
    ["INDEXED"]=>
    string(1) "0"
    ["NON_NULL"]=>
    string(1) "0"
    ["SHARED"]=>
    string(1) "0"
    ["UNIQUE"]=>
    string(1) "0"
    ["DEFAULT"]=>
    string(4) "NULL"
    ["ATTR_ORDER"]=>
    string(1) "2"
    ["CLASS_NAME"]=>
    string(5) "u1.t1"
    ["SOURCE_CLASS"]=>
    string(5) "u1.t1"
    ["IS_KEY"]=>
    string(1) "0"
    ["REMARKS"]=>
    string(0) ""
  }
  [2]=>
  array(14) {
    ["ATTR_NAME"]=>
    string(4) "col3"
    ["DOMAIN"]=>
    string(2) "12"
    ["SCALE"]=>
    string(1) "0"
    ["PRECISION"]=>
    string(2) "15"
    ["INDEXED"]=>
    string(1) "0"
    ["NON_NULL"]=>
    string(1) "0"
    ["SHARED"]=>
    string(1) "0"
    ["UNIQUE"]=>
    string(1) "0"
    ["DEFAULT"]=>
    string(4) "NULL"
    ["ATTR_ORDER"]=>
    string(1) "3"
    ["CLASS_NAME"]=>
    string(5) "u1.t1"
    ["SOURCE_CLASS"]=>
    string(5) "u1.t1"
    ["IS_KEY"]=>
    string(1) "0"
    ["REMARKS"]=>
    string(0) ""
  }
}
array(0) {
}
done!
