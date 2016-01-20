--TEST--
cubrid_prepare cubrid_bind
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

if(!$req=cubrid_prepare($conn,$sql,CUBRID_INCLUDE_OID_ERROR)){
   printf("[%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

if(!$req=cubrid_prepare($conn,"no this sql statement",CUBRID_INCLUDE_OID)){
   printf("[%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

if(!$req=cubrid_prepare($conn,$sql,CUBRID_INCLUDE_OID)){
   printf("[%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}
cubrid_execute($req);

printf("#####correct bind#####\n");
$req = cubrid_prepare($conn, 'INSERT INTO prepare_tb(c1, c2, c3, c4) VALUES(?, ?, ?, ?)');

if (!is_null($tmp = @cubrid_bind())) {
    printf('Expecting NULL, got %s\n', gettype($tmp), $tmp);
}

if (false !== ($tmp = @cubrid_bind($req, 10, 'test'))) {
    printf("Expecting boolefalse, got %s\n", gettype($tmp), $tmp);
}

cubrid_bind($req, 1, 'bind test');
cubrid_bind($req, 2, 'bind test');
cubrid_bind($req, 3, 36, 'number');
cubrid_bind($req, 4, 3.6, 'double');
cubrid_execute($req);

$req = cubrid_execute($conn, "SELECT c1, c2, c3, c4 FROM prepare_tb WHERE c1 = 'bind test'");
$result = cubrid_fetch_assoc($req);
var_dump($result);
cubrid_close_prepare($req);

if (false !== ($tmp = @cubrid_prepare($conn, "INSERT INTO prepare_tb(c1) VALUES(:%#@)"))) {
    printf("Expecting boolefalse, got %s\n", gettype($tmp), $tmp);
}

if (false !== ($tmp = @cubrid_prepare($conn, "INSERT INTO prepare_tb(c1) VALUES(:1adb)"))) {
    printf("Expecting boolefalse, got %s\n", gettype($tmp), $tmp);
}

if (false !== ($tmp = @cubrid_prepare($conn, "INSERT INTO prepare_tb(c1) VALUES(':_a-b'')"))) {
    printf("%d--Expecting boolefalse, got %s\n",__LINE__, gettype($tmp), $tmp);
}

if (false !== ($tmp = @cubrid_prepare($conn, "INSERT INTO prepare_tb(c1, c5, c6, c7) VALUES('bind time test', ':_aa', ':b3', ?)"))) {
    printf("%d--Expecting boolefalse, got %s\n",__LINE__, gettype($tmp), $tmp);
}

$req = cubrid_prepare($conn, "INSERT INTO prepare_tb(c1, c5, c6, c7) VALUES('bind time test', ?, ?, ?)");

if (false !== ($tmp = @cubrid_bind($req, ':_aaaaa', '13:15:45'))) {
    printf("%d--Expecting boolefalse, got %s\n",__LINE__, gettype($tmp), $tmp);
}

cubrid_bind($req,1, '13:15:45', 'time');
cubrid_bind($req,2, '2011-03-17');
cubrid_bind($req, 3, '13:15:45 03/17/2011');
cubrid_execute($req);
$req = cubrid_execute($conn, "SELECT c5, c6, c7 FROM prepare_tb WHERE c1 = 'bind time test'");
$result = cubrid_fetch_assoc($req);
var_dump($result);
cubrid_close_prepare($req);

//date time type
$req = cubrid_prepare($conn, "INSERT INTO prepare_tb(c1, c5,c6,c7) VALUES('time and date',?,?,?);");
cubrid_bind($req, 1, '12:00:01','time');
cubrid_bind($req, 2, '1780-02-13');
cubrid_bind($req, 3, '1989-01-03 5:35:00 pm');

cubrid_execute($req);
$req = cubrid_execute($conn, "SELECT c1,c5,c6,c7 FROM prepare_tb where c1 like 'time%';");
$result = cubrid_fetch_assoc($req);
var_dump($result);
cubrid_close_prepare($req);

cubrid_close($conn);

print 'Finished!';
?>
--CLEAN--
--EXPECTF--
Notice: Use of undefined constant CUBRID_INCLUDE_OID_ERROR - assumed 'CUBRID_INCLUDE_OID_ERROR' in %s on line %d

Warning: cubrid_prepare() expects parameter 3 to be long, string given in %s on line %d
[0] 

Warning: Error: DBMS, -493, Syntax: In line 1, column 1 before ' this sql statement'
Syntax error: unexpected 'no', expecting SELECT or VALUE or VALUES or '('  in %s on line %d
[-493] Syntax: In line 1, column 1 before ' this sql statement'
Syntax error: unexpected 'no', expecting SELECT or VALUE or VALUES or '(' 
#####correct bind#####
array(4) {
  ["c1"]=>
  string(9) "bind test"
  ["c2"]=>
  string(20) "bind test           "
  ["c3"]=>
  string(2) "36"
  ["c4"]=>
  string(18) "3.6000000000000001"
}
58--Expecting boolefalse, got resource
64--Expecting boolefalse, got NULL
array(3) {
  ["c5"]=>
  string(8) "13:15:45"
  ["c6"]=>
  string(10) "2011-03-17"
  ["c7"]=>
  string(19) "2011-03-17 13:15:45"
}
array(4) {
  ["c1"]=>
  string(13) "time and date"
  ["c5"]=>
  string(8) "12:00:01"
  ["c6"]=>
  string(10) "1780-02-13"
  ["c7"]=>
  string(19) "1989-01-03 17:35:00"
}
Finished!
