--TEST--
cubrid_get_class_name
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
$req = cubrid_execute($conn, "select * from db_class",  CUBRID_INCLUDE_OID);
if (!$req) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}

printf("#####correct example#####\n");
//$num_row=cubrid_num_rows($req);
for($i=1;$i<=10;$i++){
   $oid = cubrid_current_oid($req);
   cubrid_move_cursor($req,$i,CUBRID_CURSOR_FIRST);
   $table_name = cubrid_get_class_name($conn, $oid);
   printf("%s \n",$table_name);

}
cubrid_close_prepare($req);

printf("\n\n#####negative example#####\n");

$table_name = cubrid_get_class_name($conn, $oidd);
if(FALSE == $table_name){
   printf(printf("[001]Expect false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn)));
}else{
   print_r($table_name);
}


$table_name2 = cubrid_get_class_name($conn);
if(FALSE == $table_name2){
   printf(printf("[002]Expect false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn)));
}else{
   print_r($table_name2);
}

$table_name3 = cubrid_get_class_name();
if(FALSE == $table_name3){
   printf(printf("[003]Expect false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn)));
}else{
   print_r($table_name3);
}
print "\n";
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####correct example#####
_db_class 
_db_class 
_db_class 
_db_class 
_db_class 
_db_class 
_db_class 
_db_class 
_db_class 
_db_class 


#####negative example#####

Notice: Undefined variable: oidd in %s on line %d

Warning: Error: CCI, -20, Invalid oid string in %s on line %d
[001]Expect false [-20] [Invalid oid string]
45
Warning: cubrid_get_class_name() expects exactly 2 parameters, 1 given in %s on line %d
[002]Expect false [-20] [Invalid oid string]
45
Warning: cubrid_get_class_name() expects exactly 2 parameters, 0 given in %s on line %d
[003]Expect false [-20] [Invalid oid string]
45
Finished!
