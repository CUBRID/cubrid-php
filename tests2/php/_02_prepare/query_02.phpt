--TEST--
cubrid_query cubrid_fress_result 
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
cubrid_execute($conn, 'DROP TABLE IF EXISTS query_tb');
cubrid_execute($conn,"CREATE TABLE query_tb(id int primary key, first_name varchar(10) default 'name', last_name varchar(20),comment string SHARED 'COMMENT')");
cubrid_execute($conn,"insert into query_tb(id,first_name,last_name) values(1,'name1','last1'),(2,'name2','last2'),(3,'name3','last3')");

printf("#####negative example#####\n");
if (FALSE == ($tmp=cubrid_query())) {
    printf("[001] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

if (FALSE == ($tmp=cubrid_query("SELECT 1 AS a", $conn, "code"))) {
    printf("[002] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

if (false == ($tmp=cubrid_query('THIS IS NOT SQL', $conn))) {
    printf("[003] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

$unbuff=cubrid_query("select * from query_tb where id >10",$conn);
if (false == $unbuff) {
    printf("[004] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    $row=cubrid_fetch_assoc($unbuff);
    var_dump($row);
}


printf("#####example for cubrid_free_result()#####\n");
if(FALSE == cubrid_free_result($unbuff)){
   printf("[005] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[005] Cubrid_free_result success\n");
}

$query2=cubrid_query("select * from query_tb where id >=3");
while ($row = cubrid_fetch_assoc($query2)) {
   var_dump($row);
}
if(FALSE == cubrid_free_result($query2)){
   printf("[006]No expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[006] Cubrid_free_result success\n");
}

if(FALSE == cubrid_free_result($query2)){
   printf("[007] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[007] Cubrid_free_result success\n");
}



cubrid_close($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####negative example#####

Warning: cubrid_query() expects at least 1 parameter, 0 given in %s on line %d
[001] Expecting false, [0] []

Warning: cubrid_query() expects at most 2 parameters, 3 given in %s on line %d
[002] Expecting false, [0] []

Warning: Error: DBMS, -493, Syntax: syntax error, unexpected IdName  in %s on line %d
[003] Expecting false, [-493] [Syntax: syntax error, unexpected IdName ]
bool(false)
#####example for cubrid_free_result()#####
[005] Cubrid_free_result success
array(4) {
  ["id"]=>
  string(1) "3"
  ["first_name"]=>
  string(5) "name3"
  ["last_name"]=>
  string(5) "last3"
  ["comment"]=>
  string(7) "COMMENT"
}
[006] Cubrid_free_result success
[007] Cubrid_free_result success
Finished!

