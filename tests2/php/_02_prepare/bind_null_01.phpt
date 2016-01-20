--TEST--
cubrid_prepare cubrid_bind  APIS-397
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

cubrid_execute($conn, 'DROP TABLE IF EXISTS prepare_tb');
$sql = <<<EOD
CREATE TABLE prepare_tb(c1 string, c2 char(20), c3 int, c4 double, c5 time, c6 date, c7 TIMESTAMP,c8 bit, c9 numeric(13,4),c10 clob, c11 blob);
EOD;

if(!$req=cubrid_prepare($conn,$sql,CUBRID_INCLUDE_OID)){
   printf("[%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}
cubrid_execute($req);

printf("#####correct bind#####\n");
$req = cubrid_prepare($conn, 'INSERT INTO prepare_tb(c1, c2, c3, c4, c5, c6, c7, c8, c9, c10, c11) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

cubrid_bind($req, 1, NULL, NULL);
cubrid_bind($req, 2, NULL, NULL);
cubrid_bind($req, 3, NULL, NULL);
cubrid_bind($req, 4, NULL, NULL);
cubrid_bind($req, 5, NULL, NULL);
cubrid_bind($req, 6, NULL, NULL);
cubrid_bind($req, 7, NULL, NULL);
cubrid_bind($req, 8, NULL, NULL);
cubrid_bind($req, 9, NULL, NULL);
cubrid_bind($req, 10, NULL, NULL);
cubrid_bind($req, 11, NULL, NULL);
cubrid_execute($req);

$req = cubrid_execute($conn, "SELECT  * FROM prepare_tb ");
$result = cubrid_fetch_assoc($req);
var_dump($result);
cubrid_close_prepare($req);

cubrid_close($conn);

print 'Finished!';
?>
--CLEAN--
--EXPECTF--
#####correct bind#####
array(11) {
  ["c1"]=>
  NULL
  ["c2"]=>
  NULL
  ["c3"]=>
  NULL
  ["c4"]=>
  NULL
  ["c5"]=>
  NULL
  ["c6"]=>
  NULL
  ["c7"]=>
  NULL
  ["c8"]=>
  NULL
  ["c9"]=>
  NULL
  ["c10"]=>
  NULL
  ["c11"]=>
  NULL
}
Finished!
