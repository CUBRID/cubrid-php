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
$create_result=cubrid_query("create class set_tb(id int primary key,
        sChar set(char(10)),
	sVarchar set(varchar(10)),
	sNchar set(nchar(10)),
	sNvchar set(nchar VARYING(10)),
	sBit set(bit(10)),
	sBvit set(bit VARYING(10)),
	sNumeric set(numeric)
)");
if (!$create_result) {
    die('Create Failed: ' . cubrid_error());
}

$sql1="insert into set_tb values(1,
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
$oid = cubrid_current_oid($req);
printf("oid: %s\n",$oid);
$attr = cubrid_col_get($conn, $oid, "sNumeric");
$size = cubrid_col_size($conn, $oid, "sNumeric");
var_dump($attr);
var_dump($size);


$req = cubrid_execute($conn, "SELECT * FROM set_tb where id >2;",CUBRID_INCLUDE_OID);
$oid = cubrid_current_oid($req);
if (is_null ($oid)){
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    var_dump($oid);
}

printf("\n\n");
cubrid_close_request($req);
cubrid_disconnect($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
oid: %s
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

Warning: Error: CAS, -1012, Invalid cursor position in %s on line %d
bool(false)


Finished!
