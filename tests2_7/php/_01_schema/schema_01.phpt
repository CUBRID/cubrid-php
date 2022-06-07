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

printf("#####positive example#####\n");
$conn = cubrid_connect($host, $port, $db,  $user, $passwd);
printf("\n#####CUBRID_SCH_CLASS#####\n");
$schema1 = cubrid_schema($conn, CUBRID_SCH_CLASS);
$count=count($schema1);
printf("Number of class: %d\n",$count);
$schema1 = cubrid_schema($conn, CUBRID_SCH_CLASS,'_db_auth');
var_dump($schema1);


printf("\n#####CUBRID_SCH_VCLASS#####\n");
$schema2 = cubrid_schema($conn, CUBRID_SCH_VCLASS);
$count2=count($schema2);
printf("Number of vclass: %d\n",$count2);
$schema2 = cubrid_schema($conn, CUBRID_SCH_VCLASS,"db_auth");
var_dump($schema2);

printf("\n#####CUBRID_SCH_QUERY_SPEC#####\n");
$schema3 = cubrid_schema($conn, CUBRID_SCH_QUERY_SPEC,"_db_query_spec");
var_dump($schema3);

printf("\n#####CUBRID_SCH_ATTRIBUTE #####\n");
$schema4 = cubrid_schema($conn,CUBRID_SCH_ATTRIBUTE,"_db_data_type");
var_dump($schema4);

printf("\n#####CUBRID_SCH_METHOD  #####\n");
$schema6 = cubrid_schema($conn,CUBRID_SCH_METHOD,"_db_data_type");
var_dump($schema6);

printf("\n#####CUBRID_SCH_CLASS_METHOD #####\n");
$schema7 = cubrid_schema($conn,CUBRID_SCH_CLASS_METHOD,"_db_data_type");
var_dump($schema7);

printf("\n#####CUBRID_SCH_METHOD_FILE #####\n");
$schema8 = cubrid_schema($conn,CUBRID_SCH_METHOD_FILE,"_db_meth_file");
var_dump($schema8);

printf("\n#####CUBRID_SCH_SUPERCLASS #####\n");
$schema9 = cubrid_schema($conn,CUBRID_SCH_SUPERCLASS,"db_direct_super_class");
var_dump($schema9);

printf("\n#####CUBRID_SCH_DIRECT_SUPER_CLASS  #####\n");
$schema10 = cubrid_schema($conn,CUBRID_SCH_DIRECT_SUPER_CLASS,"db_direct_super_class");
var_dump($schema10);

printf("\n#####CUBRID_SCH_SUBCLASS#####\n");
$schema11 = cubrid_schema($conn,CUBRID_SCH_SUBCLASS,"db_direct_super_class");
var_dump($schema11);

cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####

#####CUBRID_SCH_CLASS#####
Number of class: 48
array(1) {
  [0]=>
  array(3) {
    ["NAME"]=>
    string(8) "_db_auth"
    ["TYPE"]=>
    string(1) "0"
    ["REMARKS"]=>
    NULL
  }
}

#####CUBRID_SCH_VCLASS#####
Number of vclass: 20
array(1) {
  [0]=>
  array(3) {
    ["NAME"]=>
    string(7) "db_auth"
    ["TYPE"]=>
    string(1) "0"
    ["REMARKS"]=>
    NULL
  }
}

#####CUBRID_SCH_QUERY_SPEC#####
array(0) {
}

#####CUBRID_SCH_ATTRIBUTE #####
array(2) {
  [0]=>
  array(14) {
    ["ATTR_NAME"]=>
    string(7) "type_id"
    ["DOMAIN"]=>
    string(1) "8"
    ["SCALE"]=>
    string(1) "0"
    ["PRECISION"]=>
    string(2) "10"
    ["INDEXED"]=>
    string(1) "0"
    ["NON_NULL"]=>
    string(1) "0"
    ["SHARED"]=>
    string(1) "0"
    ["UNIQUE"]=>
    string(1) "0"
    ["DEFAULT"]=>
    string(4) "NULL"
    ["ATTR_ORDER"]=>
    string(1) "1"
    ["CLASS_NAME"]=>
    string(13) "_db_data_type"
    ["SOURCE_CLASS"]=>
    string(13) "_db_data_type"
    ["IS_KEY"]=>
    string(1) "0"
    ["REMARKS"]=>
    string(0) ""
  }
  [1]=>
  array(14) {
    ["ATTR_NAME"]=>
    string(9) "type_name"
    ["DOMAIN"]=>
    string(1) "2"
    ["SCALE"]=>
    string(1) "0"
    ["PRECISION"]=>
    string(2) "16"
    ["INDEXED"]=>
    string(1) "0"
    ["NON_NULL"]=>
    string(1) "0"
    ["SHARED"]=>
    string(1) "0"
    ["UNIQUE"]=>
    string(1) "0"
    ["DEFAULT"]=>
    string(4) "NULL"
    ["ATTR_ORDER"]=>
    string(1) "2"
    ["CLASS_NAME"]=>
    string(13) "_db_data_type"
    ["SOURCE_CLASS"]=>
    string(13) "_db_data_type"
    ["IS_KEY"]=>
    string(1) "0"
    ["REMARKS"]=>
    string(0) ""
  }
}

#####CUBRID_SCH_METHOD  #####
array(0) {
}

#####CUBRID_SCH_CLASS_METHOD #####
array(0) {
}

#####CUBRID_SCH_METHOD_FILE #####
array(0) {
}

#####CUBRID_SCH_SUPERCLASS #####
array(0) {
}

#####CUBRID_SCH_DIRECT_SUPER_CLASS  #####
array(0) {
}

#####CUBRID_SCH_SUBCLASS#####
array(0) {
}
Finished!
