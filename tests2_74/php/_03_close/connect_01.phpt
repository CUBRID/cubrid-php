--TEST--
cubrid_connect
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php
include_once('connect.inc');
printf("#####positive example#####\n");
$conn1 = cubrid_connect($host, $port, $db, $user, $passwd);
if (!$conn1) {
    printf("[001] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
    exit(1);
}else{
    printf("[001] user is dba\n");
    $schema1= cubrid_schema($conn1, CUBRID_SCH_ATTR_PRIVILEGE,"db_auth","auth_type");
    var_dump($schema1);
    
}
printf("\n");

$conn2= cubrid_connect($host, $port, $db);
$schema2 = cubrid_schema($conn2, CUBRID_SCH_ATTR_PRIVILEGE,"db_auth","auth_type");
printf("[002] user is public\n");
var_dump($schema2);

$conn3 = cubrid_connect($host, $port, $db, $user);
if (FALSE == $conn3) {
    printf("[003]No Expect: return value false, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
    exit(3);
}elseif(TRUE == $conn3){
    printf("[003]Expect: return value true, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[003]no true and no false\n");
}

cubrid_close($conn1);
cubrid_close($conn2);
cubrid_close($conn3);

printf("\n\n#####negative example#####\n");
$conn4 = cubrid_connect($host, $port, $db, $user, "124456");
if (FALSE == $conn4) {
    printf("[004]Expect: return value false, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn4){
    printf("[004]No Expect: return value true, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[004]no true and no false\n");
}

$conn5 = cubrid_connect($host, $port, $db,'dbaa', $passwd);
if (FALSE == $conn5) {
    printf("[005]Expect: return value false, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn5){
    printf("[005]No Expect: return value true, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[005]no true and no false\n");
}

$conn6 = cubrid_connect($host, $port, "nothisdb");
if (FALSE == $conn6) {
    printf("[006]Expect: return value false, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn6){
    printf("[006]No Expect: return value true, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[006]no true and no false\n");
}

$conn7 = cubrid_connect($host, $port, "demodb");
if (FALSE == $conn7) {
    printf("[007]No Expect: return value false, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn7){
    printf("[007]Expect: return value true, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[007]no true and no false\n");
}


$conn8 = cubrid_connect($host, $port, demodb);
if (FALSE == $conn8) {
    printf("[008]Expect: return value false, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $conn8){
    printf("[008]No Expect: return value true, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[008]no true and no false\n");
}

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
[001] user is dba
array(7) {
  [0]=>
  array(3) {
    ["ATTR_NAME"]=>
    string(9) "auth_type"
    ["PRIVILEGE"]=>
    string(6) "SELECT"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [1]=>
  array(3) {
    ["ATTR_NAME"]=>
    string(9) "auth_type"
    ["PRIVILEGE"]=>
    string(6) "INSERT"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [2]=>
  array(3) {
    ["ATTR_NAME"]=>
    string(9) "auth_type"
    ["PRIVILEGE"]=>
    string(6) "UPDATE"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [3]=>
  array(3) {
    ["ATTR_NAME"]=>
    string(9) "auth_type"
    ["PRIVILEGE"]=>
    string(6) "DELETE"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [4]=>
  array(3) {
    ["ATTR_NAME"]=>
    string(9) "auth_type"
    ["PRIVILEGE"]=>
    string(5) "ALTER"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [5]=>
  array(3) {
    ["ATTR_NAME"]=>
    string(9) "auth_type"
    ["PRIVILEGE"]=>
    string(5) "INDEX"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [6]=>
  array(3) {
    ["ATTR_NAME"]=>
    string(9) "auth_type"
    ["PRIVILEGE"]=>
    string(7) "EXECUTE"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
}

[002] user is public
array(1) {
  [0]=>
  array(3) {
    ["ATTR_NAME"]=>
    string(9) "auth_type"
    ["PRIVILEGE"]=>
    string(6) "SELECT"
    ["GRANTABLE"]=>
    string(2) "NO"
  }
}
[003]Expect: return value true, [0] []

Warning: cubrid_close(): supplied resource is not a valid CUBRID Connect resource in %s on line %d


#####negative example#####

Warning: Error: DBMS, -171, Incorrect or missing password.%s in %s on line %d
[004]Expect: return value false, [-171] [Incorrect or missing password.%s]

Warning: Error: DBMS, -165, User "dbaa" is invalid.%s in %s on line %d
[005]Expect: return value false, [-165] [User "dbaa" is invalid.%s]

Warning: Error: DBMS, -677, Failed to connect to database server, 'nothisdb', on the following host(s): localhost:localhost%s in %s on line %d
[006]Expect: return value false, [-677] [Failed to connect to database server, 'nothisdb', on the following host(s): localhost:localhost%s]
[007]Expect: return value true, [0] []

Warning: Use of undefined constant demodb - assumed 'demodb' (this will throw an Error in a future version of PHP) in %s on line %d
[008]No Expect: return value true, [0] []
Finished!
