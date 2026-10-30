--TEST--
column
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

//Data type is numeric
$delete_result1=cubrid_query("drop class if exists numeric_tb");
if (!$delete_result1) {
    die('Delete Failed: ' . cubrid_error());
}
$create_result1=cubrid_query("create class numeric_tb(smallint_t smallint,short_t short, int_t int,bigint_t bigint,decimal_t decimal(15,2), numeric_t numeric(38,10), float_t float, real_t real, monetary_t monetary, double_t double )");
if (!$create_result1) {
    die('Create Failed: ' . cubrid_error());
}

$result1 = cubrid_execute($conn, "SELECT * FROM numeric_tb;");

$column_names1 = cubrid_column_names($result1);
$column_types1 = cubrid_column_types($result1);

printf("#####Data type is numeric#####\n");
printf("%-30s %-30s %-15s\n", "Column Names", "Column Types", "Column Maxlen");
for($i = 0, $size = count($column_names1); $i < $size; $i++) {
    $column_len1 = cubrid_field_len($result1, $i);
    printf("%-30s %-30s %-15s\n", $column_names1[$i], $column_types1[$i], $column_len1); 
}
printf("\n\n");

//Data type is character strings
$delete_result2=cubrid_query("drop class if exists character_tb");
if (!$delete_result2) {
    die('Delete Failed: ' . cubrid_error());
}
$create_result2=cubrid_query("create class character_tb(char_t char(5), varchar_t varchar(11), nchar_t nchar(20), ncharvarying_t nchar varying(536870911))");
if (!$create_result2) {
    die('Create Failed: ' . cubrid_error());
}

$result2 = cubrid_execute($conn, "SELECT * FROM character_tb;");

$column_names2 = cubrid_column_names($result2);
$column_types2 = cubrid_column_types($result2);

printf("#####Data type is character strings#####\n");
printf("%-30s %-30s %-15s\n", "Column Names", "Column Types", "Column Maxlen");
for($i = 0, $size = count($column_names2); $i < $size; $i++) {
    $column_len2 = cubrid_field_len($result2, $i);
    printf("%-30s %-30s %-15s\n", $column_names2[$i], $column_types2[$i], $column_len2);
}
printf("\n\n");

//Data type is BLOB/CLOB
$delete_result=cubrid_query("drop class if exists clob_tb");
if (!$delete_result) {
    die('Delete Failed: ' . cubrid_error());
}
$create_result=cubrid_query("create class clob_tb(id_t varchar(64) primary key, content CLOB, image BLOB)");
if (!$create_result) {
    die('Create Failed: ' . cubrid_error());
}

$result = cubrid_execute($conn, "SELECT * FROM clob_tb;");

$column_names = cubrid_column_names($result);
$column_types = cubrid_column_types($result);

printf("#####Data type is BLOB/CLOB#####\n");
printf("%-30s %-30s %-15s\n", "Column Names", "Column Types", "Column Maxlen");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $column_len = cubrid_field_len($result, $i);
    printf("%-30s %-30s %-15s\n", $column_names[$i], $column_types[$i], $column_len);
}
printf("\n\n");

//Data type is collection
$delete_result=cubrid_query("drop class if exists collection_tb");
if (!$delete_result) {
    die('Delete Failed: ' . cubrid_error());
}
$create_result=cubrid_query("create class collection_tb(sChar set(char(10)),
	sVarchar set(varchar(10)),
	sNchar set(nchar(10)),
	sNvchar set(nchar VARYING(10)),
	sBit set(bit(10)),
	sBvit set(bit VARYING(10)),
	sNumeric set(numeric),
	sInteger set(integer),
	sSmallint set(smallint),
	sMonetary set(monetary),
	sFloat set(float),
	sReal set(real),
	sDouble set(double),
	sDate set(date),
	sTime set(time),
	sTimestamp set(timestamp),
	sSet set(set),
	sMultiSet set(multiset),
	sList set(list),
	sSequence set(sequence),
        multiset_t multiset(int, CHAR(1)),
        list_t list(float, VARCHAR(1))
)");
if (!$create_result) {
    die('Create Failed: ' . cubrid_error());
}

$result = cubrid_execute($conn, "SELECT * FROM collection_tb;");

$column_names = cubrid_column_names($result);
$column_types = cubrid_column_types($result);

printf("#####Data type is collection#####\n");
printf("%-30s %-30s %-15s\n", "Column Names", "Column Types", "Column Maxlen");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $column_len = cubrid_field_len($result, $i);
    printf("%-30s %-30s %-15s\n", $column_names[$i], $column_types[$i], $column_len);
}
printf("\n\n");

//Data type is Date/Time
$delete_result=cubrid_query("drop class if exists date_tb");
if (!$delete_result) {
    die('Delete Failed: ' . cubrid_error());
}
$create_result=cubrid_query("create class date_tb(date_t date, time_t time, timestamp_t timestamp, datetime_t datetime)");
if (!$create_result) {
    die('Create Failed: ' . cubrid_error());
}

$result = cubrid_execute($conn, "SELECT * FROM date_tb;");

$column_names = cubrid_column_names($result);
$column_types = cubrid_column_types($result);

printf("#####Data type is Date/Time#####\n");
printf("%-30s %-30s %-15s\n", "Column Names", "Column Types", "Column Maxlen");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $column_len = cubrid_field_len($result, $i);
    printf("%-30s %-30s %-15s\n", $column_names[$i], $column_types[$i], $column_len);
}
printf("\n\n");

//Data type is bit strings
$delete_result=cubrid_query("drop class if exists bit_tb");
if (!$delete_result) {
    die('Delete Failed: ' . cubrid_error());
}
$create_result=cubrid_query("create class bit_tb(bit_t bit, bit2_t bit(8), bitvarying_t bit varying, bitvarying2_t bit varying(10))");
if (!$create_result) {
    die('Create Failed: ' . cubrid_error());
}

$result = cubrid_execute($conn, "SELECT * FROM bit_tb;");

$column_names = cubrid_column_names($result);
$column_types = cubrid_column_types($result);

printf("#####Data type is bit strings#####\n");
printf("%-30s %-30s %-15s\n", "Column Names", "Column Types", "Column Maxlen");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $column_len = cubrid_field_len($result, $i);
    printf("%-30s %-30s %-15s\n", $column_names[$i], $column_types[$i], $column_len);
}
printf("\n\n");


cubrid_disconnect($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
#####Data type is numeric#####
Column Names                   Column Types                   Column Maxlen  
smallint_t                     smallint                       6              
short_t                        smallint                       6              
int_t                          integer                        11             
bigint_t                       bigint                         20             
decimal_t                      numeric                        17             
numeric_t                      numeric                        40             
float_t                        float                          15             
real_t                         float                          15             
monetary_t                     monetary                       30             
double_t                       double                         29             


#####Data type is character strings#####
Column Names                   Column Types                   Column Maxlen  
char_t                         char                           5              
varchar_t                      varchar                        11             
nchar_t                        nchar                          20             
ncharvarying_t                 varnchar                       536870911      


#####Data type is BLOB/CLOB#####
Column Names                   Column Types                   Column Maxlen  
id_t                           varchar                        64             
content                        clob                           1073741823     
image                          blob                           1073741823     


#####Data type is collection#####
Column Names                   Column Types                   Column Maxlen  
schar                          set(char)                      1073741823     
svarchar                       set(varchar)                   1073741823     
snchar                         set(nchar)                     1073741823     
snvchar                        set(varnchar)                  1073741823     
sbit                           set(bit)                       1073741823     
sbvit                          set(varbit)                    1073741823     
snumeric                       set(numeric)                   1073741823     
sinteger                       set(integer)                   1073741823     
ssmallint                      set(smallint)                  1073741823     
smonetary                      set(monetary)                  1073741823     
sfloat                         set(float)                     1073741823     
sreal                          set(float)                     1073741823     
sdouble                        set(double)                    1073741823     
sdate                          set(date)                      1073741823     
stime                          set(time)                      1073741823     
stimestamp                     set(timestamp)                 1073741823     
sset                           set(unknown)                   1073741823     
smultiset                      set(unknown)                   1073741823     
slist                          set(unknown)                   1073741823     
ssequence                      set(unknown)                   1073741823     
multiset_t                     multiset(unknown)              1073741823     
list_t                         sequence(unknown)              1073741823     


#####Data type is Date/Time#####
Column Names                   Column Types                   Column Maxlen  
date_t                         date                           10             
time_t                         time                           8              
timestamp_t                    timestamp                      23             
datetime_t                     datetime                       23             


#####Data type is bit strings#####
Column Names                   Column Types                   Column Maxlen  
bit_t                          bit                            1              
bit2_t                         bit                            8              
bitvarying_t                   varbit                         1073741823     
bitvarying2_t                  varbit                         10             


Finished!



