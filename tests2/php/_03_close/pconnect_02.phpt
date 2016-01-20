--TEST--
cubrid_pconnect
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
if (!($pconn1 = cubrid_pconnect($host, $port, $db, $user, $passwd))) {
    printf("[001] Can not connect to the server using host=%s, port=%s, user=%s, passwd=***\n", $host, $port, $user);
}else{
    printf("[001]pconn values: %s \n",$pconn1);
}

$pconn4 = cubrid_pconnect($host, $port, $db,"dba","dioado");
if (FALSE == $pconn4) {
    printf("[004]Expect: return value false, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $pconn4){
    printf("[004]No Expect: return value true, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
    printf("[004]pconn: %s\n",$pconn4);
}else{
    printf("[004]no true and no false\n");
}
printf("\n\n");
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
[001]pconn values: Resource id #5 

Warning: Error: DBMS, 0, Unknown DBMS error in %s on line %d
[004]Expect: return value false, [0] [Unknown DBMS error]


Finished!
