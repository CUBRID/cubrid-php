--TEST--
cubrid_real_escape_string
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once "connect.inc";
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
$unescaped_str = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~';
$escaped_str = cubrid_real_escape_string($unescaped_str);

$len = strlen($unescaped_str);
printf("#####positive example#####\n");
cubrid_execute($conn, "DROP TABLE IF EXISTS cubrid_test");
cubrid_execute($conn, "CREATE TABLE cubrid_test (id int, t varchar($len))");
cubrid_execute($conn, "INSERT INTO cubrid_test (id,t) VALUES(1,'$escaped_str')");
$req = cubrid_execute($conn, "SELECT * FROM cubrid_test");
while($row = cubrid_fetch_assoc($req)){
   var_dump($row);
}
cubrid_free_result($req);

$unescaped2='"$unescaped_str"';
$escaped2=cubrid_real_escape_string($unescaped2,$conn);
cubrid_execute($conn, "INSERT INTO cubrid_test (id,t) VALUES(2,'$escaped2')");
$req2 = cubrid_execute($conn, "SELECT * FROM cubrid_test");
while($row = cubrid_fetch_assoc($req2)){
   var_dump($row);
}
cubrid_free_result($req2);

$unescaped3='\n\r\t-%~`!@#$%^&*()_+{}|[]:";<>,.?//*';
$escaped3=cubrid_real_escape_string($unescaped3,$conn);
cubrid_execute($conn, "INSERT INTO cubrid_test (id,t) VALUES(3,'$escaped3')");
$req3 = cubrid_execute($conn, "SELECT * FROM cubrid_test");
while($row = cubrid_fetch_assoc($req3)){
   var_dump($row);
}
cubrid_free_result($req3);

$unescaped5='     ';
$len=strlen($unescaped5);
printf("unescaped5's Len: %d\n",$len);
$escaped5=cubrid_real_escape_string($unescaped5,$conn);
cubrid_execute($conn, "INSERT INTO cubrid_test (id,t) VALUES(5,'$escaped5')");
$req5 = cubrid_execute($conn, "SELECT * FROM cubrid_test where id =5");
while($row = cubrid_fetch_assoc($req5)){
   var_dump($row);
}
cubrid_free_result($req5);


printf("\n\n#####negative example#####\n");
$unescaped4='"$unescaped_str"';
$escaped4=cubrid_real_escape_string($unescaped4,$conn,"");
if (FALSE == $escaped4) {
    printf("[001] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

$escaped6=cubrid_real_escape_string($conn);
if (FALSE == $escaped6) {
    printf("[002] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

$escaped7=cubrid_real_escape_string(NULL,$conn);
if (FALSE == $escaped7) {
    printf("[003] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    printf("[003] Expecting boolean/false, got [%s] [%s]\n", gettype($escaped7), $escaped7);
}

$escaped8=cubrid_real_escape_string("nothis string",NULL);
if (FALSE == $escaped8) {
    printf("[004] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    printf("[004] Expecting boolean/false, got [%s] [%s]\n", gettype($escaped8), $escaped8);
}


$escaped9=cubrid_real_escape_string();
if (FALSE == $escaped9) {
    printf("[005] Expecting false, [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

cubrid_close_request($req);
cubrid_disconnect($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
#####positive example#####
array(2) {
  ["id"]=>
  string(1) "1"
  ["t"]=>
  string(95) " !"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~"
}
array(2) {
  ["id"]=>
  string(1) "1"
  ["t"]=>
  string(95) " !"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~"
}
array(2) {
  ["id"]=>
  string(1) "2"
  ["t"]=>
  string(16) ""$unescaped_str""
}
array(2) {
  ["id"]=>
  string(1) "1"
  ["t"]=>
  string(95) " !"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~"
}
array(2) {
  ["id"]=>
  string(1) "2"
  ["t"]=>
  string(16) ""$unescaped_str""
}
array(2) {
  ["id"]=>
  string(1) "3"
  ["t"]=>
  string(38) "\n\r\t-%~`!@#$%^&*()_+{}|[]:";<>,.?//*"
}
unescaped5's Len: 5
array(2) {
  ["id"]=>
  string(1) "5"
  ["t"]=>
  string(5) "     "
}


#####negative example#####

Warning: cubrid_real_escape_string() expects at most 2 parameters, 3 given in %s on line %d
[001] Expecting false, [0] []

Warning: cubrid_real_escape_string() expects parameter 1 to be string, resource given in %s on line %d
[002] Expecting false, [0] []
[003] Expecting false, [0] []

Warning: cubrid_real_escape_string() expects parameter 2 to be resource, null given in %s on line %d
[004] Expecting false, [0] []

Warning: cubrid_real_escape_string() expects at least 1 parameter, 0 given in %s on line %d
[005] Expecting false, [0] []
Finished!
