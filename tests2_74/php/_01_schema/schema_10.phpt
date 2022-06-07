--TEST--
cubrid_schema CUBRID_SCH_ATTR_PRIVILEGE
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
//

printf("#####positive example#####\n");
$conn = cubrid_connect($host, $port, $db,"public", $passwd);
printf("\nCUBRID_SCH_ATTR_PRIVILEGE\n");

cubrid_execute($conn,"drop table if EXISTS privilege_tb;");
cubrid_execute($conn,"CREATE TABLE privilege_tb(album CHAR(10),dsk INTEGER);");


$schema1 = cubrid_schema($conn, CUBRID_SCH_ATTR_PRIVILEGE,"db_auth","auth_type");
var_dump($schema1);

$schema2 = cubrid_schema($conn, CUBRID_SCH_ATTR_PRIVILEGE,"privilege_tb","dsk");
var_dump($schema2);

$schema1=cubrid_schema($conn,CUBRID_SCH_CLASS_PRIVILEGE,"privilege_tb","dsk");
if ($schema1 == false) {
    printf("[001] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value1: \n");
    var_dump($schema1);
}

$schema2=cubrid_schema($conn,CUBRID_SCH_CLASS_PRIVILEGE,"privilege_tb");
if ($schema2 == false) {
    printf("[002] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value2: \n");
    var_dump($schema2);
}

printf("\n\n#####negative example#####\n");
$schema3=cubrid_schema($conn, CUBRID_SCH_ATTR_PRIVILEGE,"nothistable");
if ($schema3 == false) {
    printf("[003] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value3: \n");
    var_dump($schema3);
}

$schema4 = cubrid_schema($conn, CUBRID_SCH_ATTR_PRIVILEGE);
if ($schema4 == false) {
    printf("[004] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value4: \n");
    var_dump($schema4);
}

$schema5 = cubrid_schema($conn, CUBRID_SCH_ATTR_PRIVILEGE,"privilege_tb");
if ($schema5 == false) {
    printf("[005] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value5: \n");
    var_dump($schema5);
}

$schema6=cubrid_schema($conn,CUBRID_SCH_CLASS_PRIVILEGE,"nothistable");
if ($schema6 == false) {
    printf("[006] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value6: \n");
    var_dump($schema6);
}   

$schema7=cubrid_schema($conn,CUBRID_SCH_CLASS_PRIVILEGE);
if ($schema7 == false) {
    printf("[007] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value7: \n");
    var_dump($schema7);
}   


cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####

CUBRID_SCH_ATTR_PRIVILEGE
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
array(7) {
  [0]=>
  array(3) {
    ["ATTR_NAME"]=>
    string(3) "dsk"
    ["PRIVILEGE"]=>
    string(6) "SELECT"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [1]=>
  array(3) {
    ["ATTR_NAME"]=>
    string(3) "dsk"
    ["PRIVILEGE"]=>
    string(6) "INSERT"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [2]=>
  array(3) {
    ["ATTR_NAME"]=>
    string(3) "dsk"
    ["PRIVILEGE"]=>
    string(6) "UPDATE"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [3]=>
  array(3) {
    ["ATTR_NAME"]=>
    string(3) "dsk"
    ["PRIVILEGE"]=>
    string(6) "DELETE"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [4]=>
  array(3) {
    ["ATTR_NAME"]=>
    string(3) "dsk"
    ["PRIVILEGE"]=>
    string(5) "ALTER"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [5]=>
  array(3) {
    ["ATTR_NAME"]=>
    string(3) "dsk"
    ["PRIVILEGE"]=>
    string(5) "INDEX"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [6]=>
  array(3) {
    ["ATTR_NAME"]=>
    string(3) "dsk"
    ["PRIVILEGE"]=>
    string(7) "EXECUTE"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
}
schema value1: 
array(7) {
  [0]=>
  array(3) {
    ["CLASS_NAME"]=>
    string(19) "public.privilege_tb"
    ["PRIVILEGE"]=>
    string(6) "SELECT"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [1]=>
  array(3) {
    ["CLASS_NAME"]=>
    string(19) "public.privilege_tb"
    ["PRIVILEGE"]=>
    string(6) "INSERT"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [2]=>
  array(3) {
    ["CLASS_NAME"]=>
    string(19) "public.privilege_tb"
    ["PRIVILEGE"]=>
    string(6) "UPDATE"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [3]=>
  array(3) {
    ["CLASS_NAME"]=>
    string(19) "public.privilege_tb"
    ["PRIVILEGE"]=>
    string(6) "DELETE"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [4]=>
  array(3) {
    ["CLASS_NAME"]=>
    string(19) "public.privilege_tb"
    ["PRIVILEGE"]=>
    string(5) "ALTER"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [5]=>
  array(3) {
    ["CLASS_NAME"]=>
    string(19) "public.privilege_tb"
    ["PRIVILEGE"]=>
    string(5) "INDEX"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [6]=>
  array(3) {
    ["CLASS_NAME"]=>
    string(19) "public.privilege_tb"
    ["PRIVILEGE"]=>
    string(7) "EXECUTE"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
}
schema value2: 
array(7) {
  [0]=>
  array(3) {
    ["CLASS_NAME"]=>
    string(19) "public.privilege_tb"
    ["PRIVILEGE"]=>
    string(6) "SELECT"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [1]=>
  array(3) {
    ["CLASS_NAME"]=>
    string(19) "public.privilege_tb"
    ["PRIVILEGE"]=>
    string(6) "INSERT"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [2]=>
  array(3) {
    ["CLASS_NAME"]=>
    string(19) "public.privilege_tb"
    ["PRIVILEGE"]=>
    string(6) "UPDATE"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [3]=>
  array(3) {
    ["CLASS_NAME"]=>
    string(19) "public.privilege_tb"
    ["PRIVILEGE"]=>
    string(6) "DELETE"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [4]=>
  array(3) {
    ["CLASS_NAME"]=>
    string(19) "public.privilege_tb"
    ["PRIVILEGE"]=>
    string(5) "ALTER"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [5]=>
  array(3) {
    ["CLASS_NAME"]=>
    string(19) "public.privilege_tb"
    ["PRIVILEGE"]=>
    string(5) "INDEX"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
  [6]=>
  array(3) {
    ["CLASS_NAME"]=>
    string(19) "public.privilege_tb"
    ["PRIVILEGE"]=>
    string(7) "EXECUTE"
    ["GRANTABLE"]=>
    string(3) "YES"
  }
}


#####negative example#####
[003] Expecting false, got [0] []
[004] Expecting false, got [0] []
[005] Expecting false, got [0] []
[006] Expecting false, got [0] []
[007] Expecting false, got [0] []
Finished!
