--TEST--
cubrid_col_get cubrid_col_size and set type
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect($host, $port, $db, $user, $passwd);


//set type
$delete_result=cubrid_query("drop class if exists set_tb");
if (!$delete_result) {
    die('Delete Failed: ' . cubrid_error());
}
$create_result=cubrid_query("create class set_tb(sChar set(char(10)),
	sVarchar set(varchar(10)),
	sNchar set(nchar(10)),
	sNvchar set(nchar VARYING(10)),
	sBit set(bit(10)),
	sBvit set(bit VARYING(10)),
	sNumeric set(numeric)
) DONT_REUSE_OID");
if (!$create_result) {
    die('Create Failed: ' . cubrid_error());
}

$sql1="insert into set_tb values(
{'char1','char111'},
{'varchar1','varchar2'},
{N'aaa'},
{N'ncharvar'},
{B'11111111', B'00000011', B'0011'},
{B'11111111'},
{12341,222,444,55555}
)";
cubrid_execute($conn,$sql1);
$req = cubrid_execute($conn, "SELECT * FROM set_tb;",CUBRID_INCLUDE_OID);
cubrid_move_cursor($req, 1, CUBRID_CURSOR_FIRST);
$oid = cubrid_current_oid($req);

printf("#####correct get#####\n");
$array= array("sNchar","sBit","sNumeric");
foreach($array as $i => $value){
   $attr = cubrid_col_get($conn, $oid, $array[$i]);
   var_dump($attr);
   $size = cubrid_col_size($conn, $oid, $array[$i]);
   var_dump($size);
}


printf("\n\n#####error get#####\n");
$attr = cubrid_col_get($conn, $oid, "nothisstring");
if (is_null ($attr)){
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}
$size = cubrid_col_size($conn, $oid, "nothisstring");
if (is_null ($attr)){
    printf("[002] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

$attr = cubrid_col_get($conn,"nothisoid","sVarchar");
if (is_null ($attr)){
    printf("[003] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}
$size = cubrid_col_size($conn,"nothisoid","sVarchar");
if (is_null ($attr)){
    printf("[004] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

$attr = cubrid_col_get($conn, $oid, "");
if (is_null ($attr)){
    printf("[005] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}
$size = cubrid_col_size($conn, $oid, "");
if (is_null ($attr)){
    printf("[006] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

$attr = cubrid_col_get($conn, $oid);
if (is_null ($attr)){
    printf("[007] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}
$size = cubrid_col_size($conn, $oid);
if (is_null ($attr)){
    printf("[008] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}


printf("\n\n");
cubrid_close_request($req);
cubrid_disconnect($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
#####correct get#####
array(1) {
  [0]=>
  string(10) "aaa       "
}
int(1)
array(3) {
  [0]=>
  string(4) "0300"
  [1]=>
  string(4) "3000"
  [2]=>
  string(4) "FF00"
}
int(3)
array(4) {
  [0]=>
  string(3) "222"
  [1]=>
  string(3) "444"
  [2]=>
  string(5) "12341"
  [3]=>
  string(5) "55555"
}
int(4)


#####error get#####

Warning: Error: DBMS, -202, Attribute "nothisstring" was not found.%s in %s on line %d

Warning: Error: DBMS, -202, Attribute "nothisstring" was not found.%s in %s on line %d

Warning: Error: CCI, -20020, Invalid oid string in %s on line %d

Warning: Error: CCI, -20020, Invalid oid string in %s on line %d

Warning: Error: DBMS, -202, Attribute "" was not found.%s in %s on line %d

Warning: Error: DBMS, -202, Attribute "" was not found.%s in %s line %d

Warning: cubrid_col_get() expects exactly 3 parameters, 2 given in %s on line %d
[007] [-202] Attribute "" was not found.%s

Warning: cubrid_col_size() expects exactly 3 parameters, 2 given in %s on line %d
[008] [-202] Attribute "" was not found.%s


Finished!
