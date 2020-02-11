--TEST--
cubrid_current_oid and multiset type
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect($host, $port, $db, $user, $passwd);


//multiset and cubrid_current_oid
$delete_result=cubrid_query("drop class if exists multiset_tb");
if (!$delete_result) {
    die('Delete Failed: ' . cubrid_error());
}
$create_result=cubrid_query("create table multiset_tb(id int primary key,
        sInteger multiset(integer,monetary),
	sFloat multiset(float,date,time),
	sDouble multiset(double)
)");
if (!$create_result) {
    die('Create Failed: ' . cubrid_error());
}

$sql1="insert into multiset_tb values(1,
{11111,345,999.1111},
{234.43145,33444,DATE '08/14/1977', TIME '02:10:00'},
{4444.000,434000,114.343}
)";
$sql2="insert into multiset_tb values(2,
{1,3,4,5,23.2,43.4},
{null,null,DATE '08/14/1977', TIME '02:10:00'},
{13.00}
)";
cubrid_execute($conn,$sql1);
$req = cubrid_execute($conn, $sql2, CUBRID_INCLUDE_OID);
$oid = cubrid_current_oid($req);
if (is_null ($oid)){
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

$req = cubrid_execute($conn, "select * from multiset_tb where id > 3 ", CUBRID_INCLUDE_OID);
$oid = cubrid_current_oid($req);
if (is_null ($oid)){
    printf("[002] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}


printf("\n\n");
cubrid_close_request($req);
cubrid_disconnect($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
Warning: Error: CLIENT, -30002, Invalid API call in %s on line %d

Warning: Error: CAS, -10012, Invalid cursor position in %s on line %d


Finished!
