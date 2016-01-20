--TEST--
cubrid_next_result and cubrid_execute
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

cubrid_execute($conn, 'DROP TABLE IF EXISTS prepare_tb');
$sql = <<<EOD
CREATE TABLE prepare_tb(c1 string, c2 char(20), c3 int, c4 double);
EOD;
cubrid_execute($conn,$sql);
cubrid_execute($conn,"insert into prepare_tb values('string1','char1',1,11.11),('string2','char2',2,222.22222)");

printf("#####correct next_result#####\n");
$sql="insert into prepare_tb values('string1','char1',1,11.11);select * from prepare_tb;";
$res=cubrid_execute($conn,$sql, CUBRID_EXEC_QUERY_ALL);
if (false === $res) {
   printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[001] execute success.\n");
}
cubrid_next_result($res);
get_result_info($res);

//three sql statements
$sql5="delete from prepare_tb where c3=1;insert into prepare_tb values('string3','char3',3,333.33),('string4','char4',4,444.444); select * from prepare_tb;";
$res5=cubrid_execute($conn,$sql5, CUBRID_EXEC_QUERY_ALL);
cubrid_next_result($res5);
cubrid_next_result($res5);
while ($row = cubrid_fetch_assoc($res5)) {
   print_r($row);
}

function get_result_info($req)
{
    printf("\n------------ get_result_info --------------------\n");

    $row_num = cubrid_num_rows($req);
    $col_num = cubrid_num_cols($req);

    $column_name_list = cubrid_column_names($req);
    $column_type_list = cubrid_column_types($req);

    $column_last_name = cubrid_field_name($req, $col_num - 1);
    $column_last_table = cubrid_field_table($req, $col_num - 1);

    $column_last_type = cubrid_field_type($req, $col_num - 1);
    $column_last_len = cubrid_field_len($req, $col_num - 1);

    $column_1_flags = cubrid_field_flags($req, 1);

    printf("%-30s %d\n", "Row count:", $row_num);
    printf("%-30s %d\n", "Column count:", $col_num);
    printf("\n");

    printf("%-30s %-30s %-15s\n", "Column Names", "Column Types", "Column Len");
    printf("------------------------------------------------------------------------------\n");

    $size = count($column_name_list);
    for($i = 0; $i < $size; $i++) {
        $column_len = cubrid_field_len($req, $i);
        printf("%-30s %-30s %-15s\n", $column_name_list[$i], $column_type_list[$i], $column_len); 
    }
    printf("\n\n");

    printf("%-30s %s\n", "Last Column Name:", $column_last_name);
    printf("%-30s %s\n", "Last Column Table:", $column_last_table);
    printf("%-30s %s\n", "Last Column Type:", $column_last_type);
    printf("%-30s %d\n", "Last Column Len:", $column_last_len);
    printf("%-30s %s\n", "Second Column Flags:", $column_1_flags);

    printf("\n\n");
}

printf("\n\n#####error next_result#####\n");
//CUBRID_EXEC_QUERY_ALL no this option 
$sql2="select * from prepare_tb;select * from prepare_tb;";
$res2=cubrid_execute($conn,$sql2);
if (false === $res2) {
   printf("[002] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[002] execute success.\n");
}
if (false === ($tmp=cubrid_next_result($res2))) {
   printf("[002] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[002] next_result success.\n");

   if(is_null($row = cubrid_fetch_assoc($res2))){
      printf("[002] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
   }else{
      printf("[002] fetch success.\n");
      print_r($row);
   }
}

//resource $result is not correct
$sql3="update prepare_tb set c1= 'changestring1' where c3=1; select * from prepare_tb where c3=1;";
$res3=cubrid_execute($conn,$sql3);
if (false === $res3) {
   printf("[003] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   printf("[003] execute success.\n");
}
if (false === ($tmp=cubrid_next_result($res22222))) {
   printf("[003] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}elseif(is_null($tmp)){
    printf("%d--there is maybe some error\n",__LINE__);
}else{
   printf("[003] next_result success.\n");
}

//the second sql statement is not correct
$sql4="delete from prepare_tb where c4=222.22222; no this sql statement;";
$res4=cubrid_execute($conn,$sql4);
if (false === $res4) {
   printf("[004] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}elseif(is_null($res4)){
    printf("%d--there is maybe some error\n", __LINE__);
}else{
   printf("[004] execute success.\n");
}
if (false === ($tmp=cubrid_next_result($res4))) {
   printf("[004] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}elseif(is_null($tmp)){
    printf("%d--there is maybe some error\n", __LINE__);
}else{
   printf("[004] next_result success.\n");

   if(is_null($row = cubrid_fetch_assoc($res4))){
      printf("[004] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
   }else{
      printf("[004] fetch success.\n");
      print_r($row);
   }
}


cubrid_close($conn);
print "Finished!\n";

?>
--CLEAN--
--EXPECTF--
#####correct next_result#####
[001] execute success.

------------ get_result_info --------------------
Row count:                     3
Column count:                  4

Column Names                   Column Types                   Column Len     
------------------------------------------------------------------------------
c1                             varchar                        1073741823     
c2                             char                           20             
c3                             integer                        11             
c4                             double                         29             


Last Column Name:              c4
Last Column Table:             prepare_tb
Last Column Type:              double
Last Column Len:               29
Second Column Flags:           


Array
(
    [c1] => string2
    [c2] => char2               
    [c3] => 2
    [c4] => 222.2222199999999930
)
Array
(
    [c1] => string4
    [c2] => char4               
    [c3] => 4
    [c4] => 444.4440000000000168
)
Array
(
    [c1] => string3
    [c2] => char3               
    [c3] => 3
    [c4] => 333.3299999999999841
)


#####error next_result#####
[002] execute success.
[002] next_result success.
[002] fetch success.
Array
(
    [c1] => string2
    [c2] => char2               
    [c3] => 2
    [c4] => 222.2222199999999930
)
[003] execute success.

Notice: Undefined variable: res22222 in %s on line %d

Warning: cubrid_next_result() expects parameter 1 to be resource, null given in %s on line %d
106--there is maybe some error

Warning: Error: DBMS, -493, Syntax: In line 1, column 44 before ' this sql statement;'
Syntax error: unexpected 'no', expecting SELECT or VALUE or VALUES or '('  in %s on line %d
[004] [-493] Syntax: In line 1, column 44 before ' this sql statement;'
Syntax error: unexpected 'no', expecting SELECT or VALUE or VALUES or '(' 

Warning: cubrid_next_result() expects parameter 1 to be resource, boolean given in %s on line %d
124--there is maybe some error
Finished!
