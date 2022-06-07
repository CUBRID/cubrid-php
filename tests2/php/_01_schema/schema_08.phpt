--TEST--
cubrid_schema CUBRID_SCH_EXPORTED_KEYS
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
//CUBRID_SCH_EXPORTED_KEYS
include_once("connect.inc");
$conn = cubrid_connect_with_url($connect_url, $user, $passwd);

print("#####positive example#####\n");
printf("ssss table has two foreign keys\n"); 
cubrid_execute($conn,"drop table if EXISTS ssss;");
cubrid_execute($conn,"drop table if EXISTS aaaa;");
cubrid_execute($conn,"drop table if EXISTS album;");
cubrid_execute($conn,"CREATE TABLE album(id CHAR(10) primary key,title VARCHAR(100), artist VARCHAR(100));");
cubrid_execute($conn,"CREATE TABLE aaaa(aid CHAR(10), uid int primary key);");
cubrid_execute($conn,"CREATE TABLE ssss(album CHAR(10),dsk INTEGER,FOREIGN KEY (album) REFERENCES album(id), FOREIGN KEY (dsk) REFERENCES aaaa(uid));");

$schema1 = cubrid_schema($conn,CUBRID_SCH_EXPORTED_KEYS,"aaaa");
var_dump($schema1);

printf("\ncccc table has been referenced by two talbe as foreign key\n");
cubrid_execute($conn,"drop table if EXISTS  dddd;");
cubrid_execute($conn,"drop table if EXISTS  eeee;");
cubrid_execute($conn,"drop table if EXISTS  cccc;");
cubrid_execute($conn,"CREATE TABLE cccc(id CHAR(10) primary key,title VARCHAR(100), artist VARCHAR(100));");
cubrid_execute($conn,"CREATE TABLE eeee(aid CHAR(10),FOREIGN KEY (aid) REFERENCES cccc(id));");
cubrid_execute($conn,"CREATE TABLE dddd(album CHAR(10),dsk INTEGER,posn INTEGER, song VARCHAR(255),FOREIGN KEY (album) REFERENCES cccc(id));");


$schema2 = cubrid_schema($conn,CUBRID_SCH_EXPORTED_KEYS,"cccc");
var_dump($schema2);

print("\n#####negative example#####\n");
$schema1 =cubrid_schema($conn,CUBRID_SCH_EXPORTED_KEYS,"ssss");
if ($schema1 == false) {
    printf("[001] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value1: \n");
    var_dump($schema1);
}

$schema2 =cubrid_schema($conn,CUBRID_SCH_EXPORTED_KEYS,"nothis class_name");
if ($schema2 == false) {
    printf("[002] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value2: \n");
    var_dump($schema2);
}

$schema3 = cubrid_schema($conn,CUBRID_SCH_EXPORTED_KEYS);
if ($schema3 == false) {
    printf("[003] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value3: \n");
    var_dump($schema3);
}

$schema4=cubrid_schema($conn,CUBRID_SCH_EXPORTED_KEYS,"");
if ($schema4 == false) {
    printf("[004] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("schema value4: \n");
    var_dump($schema4);
}

cubrid_execute($conn,"drop table if EXISTS ssss;");
cubrid_execute($conn,"drop table if EXISTS aaaa;");
cubrid_execute($conn,"drop table if EXISTS album;");

cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
ssss table has two foreign keys
array(1) {
  [0]=>
  array(9) {
    ["PKTABLE_NAME"]=>
    string(4) "aaaa"
    ["PKCOLUMN_NAME"]=>
    string(3) "uid"
    ["FKTABLE_NAME"]=>
    string(8) "dba.ssss"
    ["FKCOLUMN_NAME"]=>
    string(3) "dsk"
    ["KEY_SEQ"]=>
    string(1) "1"
    ["UPDATE_RULE"]=>
    string(1) "1"
    ["DELETE_RULE"]=>
    string(1) "1"
    ["FK_NAME"]=>
    string(11) "fk_ssss_dsk"
    ["PK_NAME"]=>
    string(11) "pk_aaaa_uid"
  }
}

cccc table has been referenced by two talbe as foreign key
array(2) {
  [0]=>
  array(9) {
    ["PKTABLE_NAME"]=>
    string(4) "cccc"
    ["PKCOLUMN_NAME"]=>
    string(2) "id"
    ["FKTABLE_NAME"]=>
    string(8) "dba.dddd"
    ["FKCOLUMN_NAME"]=>
    string(5) "album"
    ["KEY_SEQ"]=>
    string(1) "1"
    ["UPDATE_RULE"]=>
    string(1) "1"
    ["DELETE_RULE"]=>
    string(1) "1"
    ["FK_NAME"]=>
    string(13) "fk_dddd_album"
    ["PK_NAME"]=>
    string(10) "pk_cccc_id"
  }
  [1]=>
  array(9) {
    ["PKTABLE_NAME"]=>
    string(4) "cccc"
    ["PKCOLUMN_NAME"]=>
    string(2) "id"
    ["FKTABLE_NAME"]=>
    string(8) "dba.eeee"
    ["FKCOLUMN_NAME"]=>
    string(3) "aid"
    ["KEY_SEQ"]=>
    string(1) "1"
    ["UPDATE_RULE"]=>
    string(1) "1"
    ["DELETE_RULE"]=>
    string(1) "1"
    ["FK_NAME"]=>
    string(11) "fk_eeee_aid"
    ["PK_NAME"]=>
    string(10) "pk_cccc_id"
  }
}

#####negative example#####
[001] Expecting false, got [0] []
[002] Expecting false, got [0] []

Warning: Error: CAS, -10004, Invalid argument in %s on line %d
[003] Expecting false, got [-10004] [Invalid argument]
[004] Expecting false, got [0] []
Finished!
