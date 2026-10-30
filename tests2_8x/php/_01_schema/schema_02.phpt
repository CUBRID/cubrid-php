--TEST--
cubrid_schema
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
//

printf("#####negative example#####\n");
$conn = cubrid_connect($host, $port, $db,  $user, $passwd);
printf("\n#####CUBRID_SCH_CLASS#####\n");
$schema1 = cubrid_schema($conn, CUBRID_SCH_CLASS,"nothis table");
if ($schema1 == false) {
    printf("[001] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value1: \n");
    var_dump($schema1);
}

printf("\n#####CUBRID_SCH_VCLASS#####\n");
$schema2 = cubrid_schema($conn, CUBRID_SCH_VCLASS,"nothistable");
if ($schema2 == false) {
    printf("[002] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value2: \n");
    var_dump($schema2);
}

printf("\n#####CUBRID_SCH_QUERY_SPEC#####\n");
$schema3 = cubrid_schema($conn, CUBRID_SCH_QUERY_SPEC,"");
if ($schema3 == false) {
    printf("[003] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value3: \n");
    var_dump($schema3);
}

printf("\n#####CUBRID_SCH_ATTRIBUTE #####\n");
$schema4 = cubrid_schema($conn,CUBRID_SCH_ATTRIBUTE,"");
if ($schema4 == false) {
    printf("[004] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value4: \n");
    var_dump($schema4);
}

printf("\n#####CUBRID_SCH_METHOD  #####\n");
$schema6 = cubrid_schema($conn,CUBRID_SCH_METHOD,"_db_data_type");
if($schema6 == false) {
    printf("[006] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value6: \n");
    var_dump($schema6);
}

printf("\n#####CUBRID_SCH_CLASS_METHOD #####\n");
$schema7 = cubrid_schema($conn,CUBRID_SCH_CLASS_METHOD,"_db_data_type");
if ($schema7 == false) {
    printf("[007] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value7: \n");
    var_dump($schema7);
}

printf("\n#####CUBRID_SCH_METHOD_FILE #####\n");
$schema8 = cubrid_schema($conn,CUBRID_SCH_METHOD_FILE,"_db_meth_file");
if ($schema8 == false) {
    printf("[008] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value8: \n");
    var_dump($schema8);
}

printf("\n#####CUBRID_SCH_SUPERCLASS #####\n");
$schema9 = cubrid_schema($conn,CUBRID_SCH_SUPERCLASS,"db_direct_super_class");
if ($schema9 == false) {
    printf("[009] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value9: \n");
    var_dump($schema9);
}


printf("\n#####CUBRID_SCH_DIRECT_SUPER_CLASS  #####\n");
$schema10 = cubrid_schema($conn,CUBRID_SCH_DIRECT_SUPER_CLASS,"db_direct_super_class");
if ($schema10 == false) {
    printf("[0010] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value10: \n");
    var_dump($schema10);
}

printf("\n#####CUBRID_SCH_SUBCLASS#####\n");
$schema11 = cubrid_schema($conn,CUBRID_SCH_SUBCLASS,"db_direct_super_class");
if ($schema11 == false) {
    printf("[0011] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value11: \n");
    var_dump($schema11);
}

cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####negative example#####

#####CUBRID_SCH_CLASS#####
[001] Expecting false, got [0] []

#####CUBRID_SCH_VCLASS#####
[002] Expecting false, got [0] []

#####CUBRID_SCH_QUERY_SPEC#####
[003] Expecting false, got [0] []

#####CUBRID_SCH_ATTRIBUTE #####
[004] Expecting false, got [0] []

#####CUBRID_SCH_METHOD  #####
[006] Expecting false, got [0] []

#####CUBRID_SCH_CLASS_METHOD #####
[007] Expecting false, got [0] []

#####CUBRID_SCH_METHOD_FILE #####
[008] Expecting false, got [0] []

#####CUBRID_SCH_SUPERCLASS #####
[009] Expecting false, got [0] []

#####CUBRID_SCH_DIRECT_SUPER_CLASS  #####
[0010] Expecting false, got [0] []

#####CUBRID_SCH_SUBCLASS#####
[0011] Expecting false, got [0] []
Finished!
