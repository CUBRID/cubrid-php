--TEST--
cubrid_connect
--SKIPIF--
--FILE--
<?php

include_once("connect.inc");

$tmp = NULL;
$conn = NULL;

if (!is_null($tmp = @cubrid_connect())) {
    printf("[001] Expecting NULL, got %s/%s\n", gettype($tmp), $tmp);
}

$conn = cubrid_connect($host, $port, $db, $user, $passwd);
if (!$conn) {
    printf("[002] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
    exit(1);
}

$conn1 = cubrid_connect($host, $port, $db, $user, $passwd, FALSE);
$conn2 = cubrid_connect($host, $port, $db, $user, $passwd, TRUE);

if ($conn != $conn1) {
    printf("[003] The new_link parameter in cubrid_connect does not work!\n");
}

if ($conn == $conn2) {
    printf("[004] Can not make a new connection with the same parameters!");
}

cubrid_close($conn);
cubrid_close($conn2);
 
// invalid db
#$conn2 = cubrid_connect('test-db-server', '33000', 'invalid_db', 'dba', '');
$conn2 = cubrid_connect($host, $port, 'invalid_db', $user, $passwd);
if($conn2 == false){
     printf("[007] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}
 
// invalid password
#$conn2 = cubrid_connect('test-db-server', '33000', 'demodb', 'dba', '222');
$conn2 = cubrid_connect($host, $port, $db, $user, '222');
if($conn2 == false){
     printf("[008] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}

$conn2 = cubrid_connect('xx.xx.xx.xx', '33000', 'invalid_db', 'dba', '');
if($conn2 == false){
	 printf("[009] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}

#$conn1 = cubrid_connect('test-db-server', '33000', 'demodb', 'invalid_user', '');
$conn1 = cubrid_connect($host, $port, $db, 'invalid_user', '');
if($conn1 == false){
     printf("[010] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}

print "done!";
?>

<?php
$user = "public_error_user";
$passwd = "";
$connect_url = "CUBRID:$host:$port:$db:::";
$skip_on_connect_failure  = getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") ? getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") : true;

$conn = cubrid_connect_with_url($connect_url, $user, $passwd);
if (!$conn) {
    printf("[005] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}

$user = "public";
$passwd = "wrong_password";
$connect_url = "CUBRID:$host:$port:$db:::";
$skip_on_connect_failure  = getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") ? getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") : true;

$conn = cubrid_connect_with_url($connect_url, $user, $passwd);
if (!$conn) {
    printf("[006] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}

?>

--CLEAN--
--EXPECTF--
Warning: Error: DBMS, -%d, Failed to connect to database server, %s
[007] [-%d] Failed to connect to database server, %s

Warning: Error: DBMS, -171, Incorrect or missing password.%s
[008] [-171] Incorrect or missing password.%s

Warning: Error: CCI, -20016, Cannot connect to CUBRID CAS in %s
[009] [-20016] Cannot connect to CUBRID CAS

Warning: Error: DBMS, -165, User "invalid_user" is invalid.%s
[010] [-165] User "invalid_user" is invalid.%s
done!

Warning: Error: DBMS, -165, User "%s" is invalid.%s in %s on line %d
[005] [-165] User "%s" is invalid.%s

Warning: Error: DBMS, -171, Incorrect or missing password.%s in %s on line %d
[006] [-171] Incorrect or missing password.%s
