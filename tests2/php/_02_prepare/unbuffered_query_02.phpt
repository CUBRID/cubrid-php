--TEST--
cubrid_unbuffered_query cubrid_free_result
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");

$tmp = NULL;
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
cubrid_execute($conn, 'DROP TABLE IF EXISTS unbuffered_tb');
cubrid_execute($conn,"CREATE TABLE unbuffered_tb(id int primary key, name varchar(10))");
cubrid_execute($conn,"insert into unbuffered_tb values(1,'name1'),(2,'name2'),(3,'name3'),(4,'name4'),(5,'name5')");

printf("#####negative example#####\n");
if (FALSE == ($tmp=cubrid_unbuffered_query())) {
    printf("[001] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

if (FALSE == ($tmp=cubrid_unbuffered_query("SELECT 1 AS a", $conn, "code"))) {
    printf("[002] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

if (false == ($tmp=cubrid_unbuffered_query('THIS IS NOT SQL', $conn))) {
    printf("[003] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

$unbuff=cubrid_unbuffered_query("select * from unbuffered_tb where id >10",$conn);
if (false == $unbuff) {
    printf("[004] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    $row=cubrid_fetch_assoc($unbuff);
    var_dump($row);
    cubrid_free_result($unbuff);
}


printf("\n\n#####negative example for cubrid_free_result()#####\n");
$free=cubrid_free_result($unbuff);
if (false == $free) {
    printf("[005] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

$free2=cubrid_free_result();
if (false == $free2) {
    printf("[006] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

$free3=cubrid_free_result("nothisresource");
if (false == $free3) {
    printf("[007] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

cubrid_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####negative example#####

Warning: cubrid_unbuffered_query() expects at least 1 parameter, 0 given in %s on line %d
[001] Expecting false, [0] []

Warning: cubrid_unbuffered_query() expects at most 2 parameters, 3 given in %s on line %d
[002] Expecting false, [0] []

Warning: Error: DBMS, -493, Syntax: In line 1, column 1 before ' IS NOT SQL'
Syntax error: unexpected 'THIS', expecting SELECT or VALUE or VALUES or '(' %s in %s on line %d
[003] Expecting false, [-493] [Syntax: In line 1, column 1 before ' IS NOT SQL'
Syntax error: unexpected 'THIS', expecting SELECT or VALUE or VALUES or '(' %s]

Warning: Error: CAS, -10006, Server handle not found in %s on line %d
[004] Expecting false, [-10006] [Server handle not found]


#####negative example for cubrid_free_result()#####

Warning: cubrid_free_result() expects parameter 1 to be resource, boolean given in %s on line %d
[005] Expecting false, [-10006] [Server handle not found]

Warning: cubrid_free_result() expects exactly 1 parameter, 0 given in %s on line %d
[006] Expecting false, [-10006] [Server handle not found]

Warning: cubrid_free_result() expects parameter 1 to be resource, string given in %s on line %d
[007] Expecting false, [-10006] [Server handle not found]
Finished!
