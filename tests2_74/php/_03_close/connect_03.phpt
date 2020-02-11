--TEST--
cubrid_connect $new_link = true
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php
include_once('connect.inc');
printf("#####positive example#####\n");
printf("\n#####new_link is true#####\n");
$conn1 = cubrid_connect($host, $port, $db, $user, $passwd,TRUE);
if (!$conn1) {
    printf("[001] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[001]conn1 values: %s \n",$conn1);
}

$conn2 = cubrid_connect($host, $port, $db, $user, $passwd,TRUE);
if (!$conn2) {
    printf("[002] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[002]conn2 values: %s \n",$conn2);
}

$conn3 = cubrid_connect($host, $port, $db, $user, $passwd,TRUE);
if (!$conn3) {
    printf("[003] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[003]conn3 values: %s \n",$conn3);
}

$conn4 = cubrid_connect($host, $port, $db, $user, $passwd,TRUE);
if (!$conn4) {
    printf("[004] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[004]conn4 values: %s \n",$conn4);
}

$close1=cubrid_close($conn1);
if($close1) {
    printf("[001]Expect close true. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[001]No Expect close false. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}

$close2=cubrid_close($conn2);
if(TRUE == $close2) {
    printf("[002]Expect close value true. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[002]No Expect close value false. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}

$close3=cubrid_close($conn3);
if(FALSE == $close3) {
    printf("[003]No Expect close value false. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE ==  $close3){
    printf("[003]Return value is true, Expect. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[003]no true and no false\n");
}

$close4=cubrid_close($conn4);
if(FALSE == $close4) {
    printf("[004]No Expect close value false. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $close4){
    printf("[004]Return value is true, Expect. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[004]no true and no false\n");
}

printf("\n\n#####negative example for connect#####\n");
$conn5 = cubrid_connect($host, $port);
if(FALSE == $close5){
    printf("[005]Expect close value false. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $close5){
    printf("[005]Return value is true, No Expect. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[005]no true and no false\n");
}

$conn6 = cubrid_connect($host);
if(FALSE == $close6) {
    printf("[006]Expect close value false. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $close6){
    printf("[006]Return value is true, No Expect. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[006]no true and no false\n");
}

$conn7 = cubrid_connect();
if(FALSE == $close7) {
    printf("[007]Expect close value false. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $close7){
    printf("[007]Return value is true, No Expect. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[007]no true and no false\n");
}



print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####

#####new_link is true#####
[001]conn1 values: Resource id #5 
[002]conn2 values: Resource id #6 
[003]conn3 values: Resource id #7 
[004]conn4 values: Resource id #8 
[001]Expect close true. [0] 
[002]Expect close value true. [0] 
[003]Return value is true, Expect. [0] 
[004]Return value is true, Expect. [0] 


#####negative example for connect#####

Warning: cubrid_connect() expects at least 3 parameters, 2 given in %s on line %d

Notice: Undefined variable: close5 in %s on line %d
[005]Expect close value false. [0] 

Warning: cubrid_connect() expects at least 3 parameters, 1 given in %s on line %d

Notice: Undefined variable: close6 in %s on line %d
[006]Expect close value false. [0] 

Warning: cubrid_connect() expects at least 3 parameters, 0 given in %s on line %d

Notice: Undefined variable: close7 in %s on line %d
[007]Expect close value false. [0] 
Finished!
