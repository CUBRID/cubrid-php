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
printf("#####positive example#####\n");
if (!($pconn1 = cubrid_pconnect($host, $port, $db, $user, $passwd))) {
    printf("[001] Can not connect to the server using host=%s, port=%s, user=%s, passwd=***\n", $host, $port, $user);
}else{
    printf("[001]pconn values: %s \n",$pconn1);
}
if (!($pconn2 = cubrid_pconnect($host, $port, $db, $user))) {
    printf("[002] Can not connect to the server using host=%s, port=%s, user=%s, passwd=***\n", $host, $port, $user);
}else{
    printf("[002]pconn values: %s \n",$pconn2);
}

if (!($pconn3 = cubrid_pconnect($host, $port, $db))) {
	printf("[003] Can not connect to the server using host=%s, port=%s, user=%s, passwd=***\n", $host, $port, $user);
}else{
    printf("[003]pconn values: %s \n",$pconn3);
}

cubrid_close($pconn1);
cubrid_close($pconn2);
cubrid_close($pconn3);

printf("\n\n#####negative example#####\n");
$pconn4 = cubrid_pconnect($host, $port, $db, $user, "124456");
if (FALSE == $pconn4) {
    printf("[004]Expect: return value false, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $pconn4){
    printf("[004]No Expect: return value true, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[004]no true and no false\n");
}

$pconn5 = cubrid_pconnect($host, $port, $db,'dbaa', $passwd);
if (FALSE == $pconn5) {
    printf("[005]Expect: return value false, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $pconn5){
    printf("[005]No Expect: return value true, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[005]no true and no false\n");
}

$pconn6 = cubrid_pconnect($host, $port, "nothisdb");
if (FALSE == $pconn6) {
    printf("[006]Expect: return value false, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $pconn6){
    printf("[006]No Expect: return value true, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[006]no true and no false\n");
}

$pconn7 = cubrid_pconnect($host, $port, "demodb");
if (FALSE == $pconn7) {
    printf("[007]No Expect: return value false, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $pconn7){
    printf("[007]Expect: return value true, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[007]no true and no false\n");
}


$pconn8 = cubrid_pconnect($host, $port,$phpdb);
if (FALSE == $pconn8) {
    printf("[008]Expect: return value false, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $pconn8){
    printf("[008]No Expect: return value true, [%d] [%s]\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[008]no true and no false\n");
}



print "Finished!\n";
?>
--CLEAN--
--XFAIL--
cubrid php driver do not support detail error message. cubrid php driver 9.0.0 resolved this issue
--EXPECTF--
#####positive example#####
[001]pconn values: Resource id #5 
[002]pconn values: Resource id #6 
[003]pconn values: Resource id #7 


#####negative example#####

Warning: Error: DBMS, -171, Incorrect or missing password. in %s on line %d
[004]Expect: return value false, [-171] [Incorrect or missing password.]

Warning: Error: DBMS, -165, User "dbaa" is invalid. in %s on line %d
[005]Expect: return value false, [-165] [User "dbaa" is invalid.]

Warning: Error: DBMS, -677, Failed to connect to database server, 'nothisdb', on the following host(s): localhost:localhost in %s on line %d
[006]Expect: return value false, [-677] [Failed to connect to database server, 'nothisdb', on the following host(s): localhost:localhost]
[007]Expect: return value true, [0] []

Notice: Undefined variable: phpdb in %s on line %d

Warning: Error: CAS, -1, Unknown error message in %s on line %d
[008]Expect: return value false, [-1] [Unknown error message]
Finished!
