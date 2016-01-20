--TEST-- 
cubrid_connect $new_link = false cubrid_disconnect cubrid_close
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php
include_once('connect.inc');
printf("#####positive example#####\n");
printf("\n#####new_link is false#####\n");
$conn1 = cubrid_connect($host, $port, $db, $user, $passwd,FALSE);
if (!$conn1) {
    printf("[001] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[001]conn1 values: %s \n",$conn1);
}

$conn2 = cubrid_connect($host, $port, $db, $user, $passwd,FALSE);
if (!$conn2) {
    printf("[002] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[002]conn2 values: %s \n",$conn2);
}

$conn3 = cubrid_connect($host, $port, $db, $user, $passwd,FALSE);
if (!$conn3) {
    printf("[003] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[003]conn3 values: %s \n",$conn3);
}

$conn4 = cubrid_connect($host, $port, $db, $user, $passwd,FALSE);
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
if(FALSE == $close2) {
    printf("[002]No Expect close value false. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[002]Expect close value true. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
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

printf("\n\n#####negative example for disconnect and close#####\n");
$conn5 = cubrid_connect($host, $port, $db, $user, $passwd);
$disconn5 = cubrid_disconnect($conn5,NULL);
if(FALSE == $disconn5){
    printf("[005]Expect close value false. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $disconn5 ){
    printf("[005]Return value is true, No Expect. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[005]no true and no false\n");
}

$disconn6 = cubrid_disconnect(NULL);
if(FALSE == $disconn6){
    printf("[006]Expect close value false. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $disconn6 ){
    printf("[006]Return value is true, No Expect. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[006]no true and no false\n");
}

$disconn7 = cubrid_disconnect();
if(FALSE == $disconn7){
    printf("[007]Expect close value false. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $disconn7 ){
    printf("[007]Return value is true, No Expect. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[007]no true and no false\n");
}

$close8 = cubrid_close($conn5,NULL);
if(FALSE == $close8){
    printf("[008]Expect close value false. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $disconn8 ){
    printf("[008]Return value is true, No Expect. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[008]no true and no false\n");
}

$close9 = cubrid_close(NULL);
if(FALSE == $close9){
    printf("[009]Expect close value false. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $close9 ){
    printf("[009]Return value is true, No Expect. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[009]no true and no false\n");
}

$close10 = cubrid_close($conn5);
if(FALSE == $close10){
    printf("[0010]No Expect close value false. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}elseif(TRUE == $close10){
    printf("[0010]Return value is true, Expect. [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
}else{
    printf("[0010]no true and no false\n");
}
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####

#####new_link is false#####
[001]conn1 values: Resource id #5 
[002]conn2 values: Resource id #5 
[003]conn3 values: Resource id #5 
[004]conn4 values: Resource id #5 
[001]Expect close true. [0] 
[002]Expect close value true. [0] 
[003]Return value is true, Expect. [0] 
[004]Return value is true, Expect. [0] 


#####negative example for disconnect and close#####

Warning: cubrid_disconnect() expects at most 1 parameter, 2 given in %s on line %d
[005]Expect close value false. [0] 

Warning: cubrid_disconnect() expects parameter 1 to be resource, null given in %s on line %d
[006]Expect close value false. [0] 
[007]Return value is true, No Expect. [0] 

Warning: cubrid_close() expects at most 1 parameter, 2 given in %s on line %d
[008]Expect close value false. [0] 

Warning: cubrid_close() expects parameter 1 to be resource, null given in %s on line %d
[009]Expect close value false. [0] 
[0010]Return value is true, Expect. [0] 
Finished!
