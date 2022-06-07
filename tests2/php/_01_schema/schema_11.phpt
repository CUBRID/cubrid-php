--TEST--
cubrid_schema CUBRID_SCH_TRIGGER
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
//CUBRID_SCH_TRIGGER
include_once("connect.inc");
$conn = cubrid_connect_with_url($connect_url, $user, $passwd);

cubrid_execute($conn,"drop class if exists hi");
cubrid_execute($conn,"drop class if exists tt1");
cubrid_execute($conn,"drop class if exists tt2");
cubrid_execute($conn,"drop class if exists tt3");
cubrid_execute($conn,"create class hi ( a int , b string )");
cubrid_execute($conn,"create class tt1( a int, b string )");
cubrid_execute($conn,"create class tt2( a int, b string )");
cubrid_execute($conn,"create class tt3( a int, b string )");
cubrid_execute($conn,"create trigger tt1_insert after insert on tt1 execute insert into hi(a, b) values( obj.a ,to_char(obj.a))");
cubrid_execute($conn,"create trigger tt2_delete before delete on tt2 execute delete from hi");
cubrid_execute($conn,"create trigger tt3_alter before update on tt3 execute  update hi set a=a-100 ") ;
cubrid_execute($conn,"insert into tt1(a,b) values(1, 'test');");
//cubrid_execute($conn,"");

print("#####positive example#####\n");
printf("Owner name for class_name\n");
$trriger0 = cubrid_schema($conn,CUBRID_SCH_TRIGGER,"dba.tt1");
var_dump($trriger0);

printf("No owner name for class_name\n");
$trriger1 = cubrid_schema($conn,CUBRID_SCH_TRIGGER,"tt1");
var_dump($trriger1);

printf("no parameter for class_name\n");
$trriger2= cubrid_schema($conn,CUBRID_SCH_TRIGGER);
var_dump($trriger2);


print("#####negative example#####\n");
cubrid_disconnect($conn);

$trriger3 = cubrid_schema($conn,CUBRID_SCH_TRIGGER,"tt1");
if ($trriger3 == false) {
    printf("[001] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("trigger value1: \n");
    var_dump($trriger3);
}

$disconnet=cubrid_disconnect($conn);
if ($disconnet== false) {
    printf("[002] Expecting false, got [%d] [%s]\n",cubrid_error_code(),cubrid_error_msg());
}else{
    printf("disconnect value1: \n");
    var_dump($disconnet);
}

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
Owner name for class_name
array(1) {
  [0]=>
  array(11) {
    ["NAME"]=>
    string(14) "dba.tt1_insert"
    ["STATUS"]=>
    string(6) "ACTIVE"
    ["EVENT"]=>
    string(6) "INSERT"
    ["TARGET_CLASS"]=>
    string(7) "dba.tt1"
    ["TARGET_ATTR"]=>
    string(0) ""
    ["ACTION_TIME"]=>
    string(5) "AFTER"
    ["ACTION"]=>
    string(71) "insert into [dba.hi] ([a], [b]) values ([obj].[a],  to_char([obj].[a]))"
    ["PRIORITY"]=>
    string(8) "0.000000"
    ["CONDITION_TIME"]=>
    string(0) ""
    ["CONDITION"]=>
    string(0) ""
    ["REMARKS"]=>
    string(0) ""
  }
}
No owner name for class_name
array(0) {
}
no parameter for class_name
array(3) {
  [0]=>
  array(11) {
    ["NAME"]=>
    string(13) "dba.tt3_alter"
    ["STATUS"]=>
    string(6) "ACTIVE"
    ["EVENT"]=>
    string(6) "UPDATE"
    ["TARGET_CLASS"]=>
    string(7) "dba.tt3"
    ["TARGET_ATTR"]=>
    string(0) ""
    ["ACTION_TIME"]=>
    string(6) "BEFORE"
    ["ACTION"]=>
    string(31) "update [dba.hi] set [a]=[a]-100"
    ["PRIORITY"]=>
    string(8) "0.000000"
    ["CONDITION_TIME"]=>
    string(0) ""
    ["CONDITION"]=>
    string(0) ""
    ["REMARKS"]=>
    string(0) ""
  }
  [1]=>
  array(11) {
    ["NAME"]=>
    string(14) "dba.tt2_delete"
    ["STATUS"]=>
    string(6) "ACTIVE"
    ["EVENT"]=>
    string(6) "DELETE"
    ["TARGET_CLASS"]=>
    string(7) "dba.tt2"
    ["TARGET_ATTR"]=>
    string(0) ""
    ["ACTION_TIME"]=>
    string(6) "BEFORE"
    ["ACTION"]=>
    string(29) "delete [dba.hi] from [dba.hi]"
    ["PRIORITY"]=>
    string(8) "0.000000"
    ["CONDITION_TIME"]=>
    string(0) ""
    ["CONDITION"]=>
    string(0) ""
    ["REMARKS"]=>
    string(0) ""
  }
  [2]=>
  array(11) {
    ["NAME"]=>
    string(14) "dba.tt1_insert"
    ["STATUS"]=>
    string(6) "ACTIVE"
    ["EVENT"]=>
    string(6) "INSERT"
    ["TARGET_CLASS"]=>
    string(7) "dba.tt1"
    ["TARGET_ATTR"]=>
    string(0) ""
    ["ACTION_TIME"]=>
    string(5) "AFTER"
    ["ACTION"]=>
    string(71) "insert into [dba.hi] ([a], [b]) values ([obj].[a],  to_char([obj].[a]))"
    ["PRIORITY"]=>
    string(8) "0.000000"
    ["CONDITION_TIME"]=>
    string(0) ""
    ["CONDITION"]=>
    string(0) ""
    ["REMARKS"]=>
    string(0) ""
  }
}
#####negative example#####

Warning: cubrid_schema(): 5 is not a valid CUBRID-Connect resource in %s on line %d
[001] Expecting false, got [0] []

Warning: cubrid_disconnect(): 5 is not a valid CUBRID-Connect resource in %s on line %d
[002] Expecting false, got [0] []
Finished!
