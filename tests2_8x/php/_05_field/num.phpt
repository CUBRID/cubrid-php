--TEST--
cubrid_num_rows 
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

//Row of selecting result is one
$delete_result1=cubrid_query("drop class if exists numeric_tb");
if (!$delete_result1) {
    die('Delete Failed: ' . cubrid_error());
}
$create_result1=cubrid_query("create class numeric_tb(smallint_t smallint,short_t short, int_t int,bigint_t bigint,decimal_t decimal(15,2), numeric_t numeric(38,10), float_t float, real_t real, monetary_t monetary, double_t double )");
if (!$create_result1) {
    die('Create Failed: ' . cubrid_error());
}

$sql_statment1="insert into numeric_tb values(-32768,32767,2147483647,-9223372036854775808,0.12345678,12345.6789,-3.402823466E+38,+3.402823466E+38,-3.402823466E+38,-1.7976931348623157E+308);";
$sql_statement2="SELECT * FROM numeric_tb;";
$insert_result=cubrid_query($sql_statment1);

$res = cubrid_execute($conn, $sql_statement2);

$row_num = cubrid_num_rows($res);
if ($row_num < 0) {
	return false;
}
assert($row_num ==1 );
$col_num = cubrid_num_cols($res);
if ($col_num < 0) {
	return false;
}
$field_num = cubrid_num_fields($res);
if ($col_num < 0) {
	return false;
}
assert($field_num == $col_num);
printf("Values of column: %d\n",$field_num);


//Row of selecting result is null
$delete_result=cubrid_query("drop class if exists date_tb");
if (!$delete_result) {
    die('Delete Failed: ' . cubrid_error());
}
$create_result=cubrid_query("create class date_tb(date_t date, time_t time, timestamp_t timestamp, datetime_t datetime)");
if (!$create_result) {
    die('Create Failed: ' . cubrid_error());
}
$sql1="insert into date_tb values( DATE '08/14/1977', TIME '02:10:00', TIMESTAMP '08/14/1977 5:35:00 pm',DATETIME '10/31'),( DATE '08/14/1977', TIME '02:10:00', TIMESTAMP '08/15/1977 5:35:00 pm',DATETIME '13:15:45 10/31/2008'),( null, null, null,DATETIME '10/31/2008 01:15:45 PM')";
$sql2="select date_t,time_t,datetime_t from date_tb  where date_t = date_t order by 1,2,3";
$sql3="select date_t,time_t,datetime_t from date_tb  where date_t <> date_t order by 1,2,3";
$result1=cubrid_query($sql1);
$result2=cubrid_query($sql2);
$result3=cubrid_query($sql3);

$row_num = cubrid_num_rows($result2);
assert($row_num ==2 );
$col_num = cubrid_num_cols($result2);
$field_num = cubrid_num_fields($result2);
assert($field_num == $col_num);
printf("Values of column: %d\n",$field_num);

$row_num = cubrid_num_rows($result3);
assert($row_num ==0 );
$col_num = cubrid_num_cols($result3);
$field_num = cubrid_num_fields($result3);
assert($field_num == $col_num);
printf("Values of column: %d\n",$field_num);

cubrid_disconnect($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
Values of column: 10
Values of column: 3
Values of column: 3
Finished!
