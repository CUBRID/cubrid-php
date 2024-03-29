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
$delete_result=cubrid_query("drop class if exists numeric_tb");
if (!$delete_result) {
    die('Delete Failed: ' . cubrid_error());
}
$create_result=cubrid_query("create class numeric_tb(smallint_t smallint,short_t short, int_t int,bigint_t bigint,decimal_t decimal(15,2), numeric_t numeric(38,10), float_t float, real_t real, monetary_t monetary, double_t double )");
if (!$create_result) {
    die('Create Failed: ' . cubrid_error());
}

$result = cubrid_execute($conn, "SELECT * FROM numeric_tb;");

$column_names = cubrid_column_names($result);
$column_types = cubrid_column_types($result);

printf("#####Data type is numeric#####\n");
printf("%-15s %-15s %s\n", "Field Table", "Field Name", "Field Type");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $column_len = cubrid_field_len($result, $i);
    assert($column_names[$i] == cubrid_field_name($result, $i));
    assert($column_types[$i] == cubrid_field_type($result, $i));
    printf("%-30s %-30s %-15s\n", cubrid_field_table($result, $i),cubrid_field_name($result, $i),cubrid_field_type($result, $i)); 
}
printf("\n\n");

//Data type is character strings
$delete_result=cubrid_query("drop class if exists character_tb");
if (!$delete_result) {
    die('Delete Failed: ' . cubrid_error());
}
$create_result=cubrid_query("create class character_tb(char_t char(5), varchar_t varchar(11), nchar_t nchar(20), ncharvarying_t nchar varying(536870911))");
if (!$create_result) {
    die('Create Failed: ' . cubrid_error());
}

$result = cubrid_execute($conn, "SELECT * FROM character_tb;");

$column_names = cubrid_column_names($result);
$column_types = cubrid_column_types($result);

printf("#####Data type is character strings#####\n");
printf("%-15s %-15s %s\n", "Field Table", "Field Name", "Field Type");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $column_len = cubrid_field_len($result, $i);
    assert($column_names[$i] == cubrid_field_name($result, $i));
    assert($column_types[$i] == cubrid_field_type($result, $i));
    printf("%-30s %-30s %-15s\n", cubrid_field_table($result, $i),cubrid_field_name($result, $i),cubrid_field_type($result, $i));         
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
printf("%-15s %-15s %s\n", "Field Table", "Field Name", "Field Type");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $column_len = cubrid_field_len($result, $i);
    assert($column_names[$i] == cubrid_field_name($result, $i));
    assert($column_types[$i] == cubrid_field_type($result, $i));
    printf("%-30s %-30s %-15s\n", cubrid_field_table($result, $i),cubrid_field_name($result, $i),cubrid_field_type($result, $i));         
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
printf("%-15s %-15s %s\n", "Field Table", "Field Name", "Field Type");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $column_len = cubrid_field_len($result, $i);
    assert($column_names[$i] == cubrid_field_name($result, $i));
    assert($column_types[$i] == cubrid_field_type($result, $i));
    printf("%-30s %-30s %-15s\n", cubrid_field_table($result, $i),cubrid_field_name($result, $i),cubrid_field_type($result, $i));         
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
printf("%-15s %-15s %s\n", "Field Table", "Field Name", "Field Type");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $column_len = cubrid_field_len($result, $i);
    assert($column_names[$i] == cubrid_field_name($result, $i));
    assert($column_types[$i] == cubrid_field_type($result, $i));
    printf("%-30s %-30s %-15s\n", cubrid_field_table($result, $i),cubrid_field_name($result, $i),cubrid_field_type($result, $i));         
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
printf("%-15s %-15s %s\n", "Field Table", "Field Name", "Field Type");
for($i = 0, $size = count($column_names); $i < $size; $i++) {
    $column_len = cubrid_field_len($result, $i);
    assert($column_names[$i] == cubrid_field_name($result, $i));
    assert($column_types[$i] == cubrid_field_type($result, $i));
    printf("%-30s %-30s %-15s\n", cubrid_field_table($result, $i),cubrid_field_name($result, $i),cubrid_field_type($result, $i));
}
printf("\n\n");


cubrid_disconnect($conn);
printf("Finished!\n");
?>
--CLEAN--
--EXPECTF--
#####Data type is numeric#####
Field Table     Field Name      Field Type
dba.numeric_tb                 smallint_t                     smallint       
dba.numeric_tb                 short_t                        smallint       
dba.numeric_tb                 int_t                          integer        
dba.numeric_tb                 bigint_t                       bigint         
dba.numeric_tb                 decimal_t                      numeric        
dba.numeric_tb                 numeric_t                      numeric        
dba.numeric_tb                 float_t                        float          
dba.numeric_tb                 real_t                         float          
dba.numeric_tb                 monetary_t                     monetary       
dba.numeric_tb                 double_t                       double         


#####Data type is character strings#####
Field Table     Field Name      Field Type
dba.character_tb               char_t                         char           
dba.character_tb               varchar_t                      varchar        
dba.character_tb               nchar_t                        nchar          
dba.character_tb               ncharvarying_t                 varnchar       


#####Data type is BLOB/CLOB#####
Field Table     Field Name      Field Type
dba.clob_tb                    id_t                           varchar        
dba.clob_tb                    content                        clob           
dba.clob_tb                    image                          blob           


#####Data type is collection#####
Field Table     Field Name      Field Type
dba.collection_tb              schar                          set(char)      
dba.collection_tb              svarchar                       set(varchar)   
dba.collection_tb              snchar                         set(nchar)     
dba.collection_tb              snvchar                        set(varnchar)  
dba.collection_tb              sbit                           set(bit)       
dba.collection_tb              sbvit                          set(varbit)    
dba.collection_tb              snumeric                       set(numeric)   
dba.collection_tb              sinteger                       set(integer)   
dba.collection_tb              ssmallint                      set(smallint)  
dba.collection_tb              smonetary                      set(monetary)  
dba.collection_tb              sfloat                         set(float)     
dba.collection_tb              sreal                          set(float)     
dba.collection_tb              sdouble                        set(double)    
dba.collection_tb              sdate                          set(date)      
dba.collection_tb              stime                          set(time)      
dba.collection_tb              stimestamp                     set(timestamp) 
dba.collection_tb              sset                           set(unknown)   
dba.collection_tb              smultiset                      set(unknown)   
dba.collection_tb              slist                          set(unknown)   
dba.collection_tb              ssequence                      set(unknown)   
dba.collection_tb              multiset_t                     multiset(unknown)
dba.collection_tb              list_t                         sequence(unknown)


#####Data type is Date/Time#####
Field Table     Field Name      Field Type
dba.date_tb                    date_t                         date           
dba.date_tb                    time_t                         time           
dba.date_tb                    timestamp_t                    timestamp      
dba.date_tb                    datetime_t                     datetime       


#####Data type is bit strings#####
Field Table     Field Name      Field Type
dba.bit_tb                     bit_t                          bit            
dba.bit_tb                     bit2_t                         bit            
dba.bit_tb                     bitvarying_t                   varbit         
dba.bit_tb                     bitvarying2_t                  varbit         


Finished!
