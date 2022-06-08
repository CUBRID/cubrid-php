--TEST--
cubrid_execute
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
cubrid_execute($conn,"insert into prepare_tb values('string1','char2',1,11.11,TIME '02:10:00',DATE '08/14/1977', TIMESTAMP '08/14/1977 5:35:00 pm',B'1',432341.4321, CHAR_TO_CLOB('This is a Dog'), BIT_TO_BLOB(X'000001'))");

printf("#####error execute#####\n");
$req = cubrid_prepare($conn, 'INSERT INTO prepare_tb(c1) VALUES(?)');
cubrid_bind($req, 1, 'bind test');
if (false ==($tmp =cubrid_execute($req11111))) {
   printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[001] execute success.\n");
   $select=cubrid_execute($conn,"select c1 from prepare_tb");
   $result = cubrid_fetch_assoc($select);
   var_dump($result);
}

if (false ==($tmp =cubrid_execute($conn,"nothissql"))) {
   printf("[002] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[002] execute success.\n");
}

$req3 = cubrid_prepare($conn, "select c1,c2,c3,c4,c5,c6,c7,c8,c9 from prepare_tb where c1 like ? ");
cubrid_bind($req3, 1, 'string%');
if (false ==($tmp =cubrid_execute($req3,CUBRID_INCLUDE_OID))) {
   printf("[003] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[003] execute success.\n");
   $result = cubrid_fetch_assoc($req3);
   var_dump($result);
}

$req3 = cubrid_prepare($conn, "select * from prepare_tb where c4=? ");
cubrid_bind($req3, 1, 11.11);
if (false ==($tmp =cubrid_execute($req3,CUBRID_INCLUDE_OIDoooo))) {
   printf("[004] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[004] execute success.\n");
   $select=cubrid_execute($conn,"select * from prepare_tb");
   $result = cubrid_fetch_assoc($select);
   var_dump($result);
}

if (false ==($tmp =cubrid_execute($connn,"select * from prepare_tb where c4=11.11"))) {
   printf("[005] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[005] execute success.\n");
   $result = cubrid_fetch_assoc($tmp);
   var_dump($result);
}

cubrid_close($conn);
print 'Finished!';
?>
--CLEAN--
--EXPECTF--
#####error execute#####

Notice: Undefined variable: req11111 in %s on line %d

Warning: cubrid_execute() expects parameter 1 to be resource, null given in %s on line %d
[001] [0] 

Warning: Error: DBMS, -493, Syntax: In line 1, column 1 before END OF STATEMENT
Syntax error: unexpected 'nothissql', expecting SELECT or VALUE or VALUES or '(' %s in %s on line %d
[002] [-493] Syntax: In line 1, column 1 before END OF STATEMENT
Syntax error: unexpected 'nothissql', expecting SELECT or VALUE or VALUES or '(' %s
[003] execute success.
array(9) {
  ["c1"]=>
  string(7) "string1"
  ["c2"]=>
  string(20) "char2               "
  ["c3"]=>
  string(1) "1"
  ["c4"]=>
  string(19) "11.109999999999999%d"
  ["c5"]=>
  string(8) "02:10:00"
  ["c6"]=>
  string(10) "1977-08-14"
  ["c7"]=>
  string(19) "1977-08-14 17:35:00"
  ["c8"]=>
  string(2) "80"
  ["c9"]=>
  string(11) "432341.4321"
}

Notice: Use of undefined constant CUBRID_INCLUDE_OIDoooo - assumed 'CUBRID_INCLUDE_OIDoooo' in %s on line %d

Warning: cubrid_execute(): supplied resource is not a valid CUBRID-Connect resource in %s on line %d
[004] [0] 

Notice: Undefined variable: connn in %s on line %d

Warning: cubrid_execute() expects parameter 1 to be resource, null given in %s on line %d
[005] [0] 
Finished!
