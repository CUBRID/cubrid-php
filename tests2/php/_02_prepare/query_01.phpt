--TEST--
cubrid_query cubrid_fress_result cubrid_real_escape_string
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
cubrid_execute($conn, 'DROP TABLE IF EXISTS query_tb');
cubrid_execute($conn,"CREATE TABLE query_tb(id int primary key, first_name varchar(10) default 'name', last_name varchar(20),comment string SHARED 'COMMENT')");
cubrid_execute($conn,"insert into query_tb(id,first_name,last_name) values(1,'name1','last1'),(2,'name2','last2'),(3,'name3','last3')");

printf("#####positive example#####\n");
$firstname = 'name1';
$lastname  = 'last1';
$sql= sprintf("select * from query_tb where first_name='%s' and last_name ='%s'",
cubrid_real_escape_string($firstname),
cubrid_real_escape_string($lastname));

//select statement
$query1=cubrid_query($sql);
while ($row = cubrid_fetch_assoc($query1)) {
    printf("%s \n", $row['first_name']); 
    printf("%s\n", $row['last_name']);
    printf("%s\n",$row['id']);
}
cubrid_free_result($query1);

$query2=cubrid_query("select * from query_tb ",$conn);
while($result2=cubrid_fetch_array($query2,CUBRID_NUM)){
   var_dump($result2);
}
cubrid_free_result($query2);

printf("\n\n#####show statement#####\n");
cubrid_execute($conn, "drop table if EXISTS query_tb2");
cubrid_execute($conn, "CREATE TABLE query_tb2(id INT, phone VARCHAR(10),address string,email char(30),coment string );");
cubrid_execute($conn, "create index index1 on query_tb2(id)");
cubrid_execute($conn, "create reverse unique index reverse_unique_index on query_tb2(phone)");
cubrid_execute($conn, "create reverse index reverse_index on query_tb2(address)");
cubrid_execute($conn, "create unique index unique_index on query_tb2(email)");
$query3=cubrid_query("show index in query_tb2;");
while($result3=cubrid_fetch_array($query3,CUBRID_ASSOC)){
   print_r($result3);
}
cubrid_free_result($query3);

printf("\n\n#####describe#####\n");
$query4=cubrid_query("describe query_tb2;");
if (FALSE == $query4) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    while($row=cubrid_fetch_assoc($query4)){
       var_dump($row);
    }
}
cubrid_free_result($query4);

printf("\n\n#####explan#####\n");
$query5=cubrid_query("explain query_tb;");
while($result5=cubrid_fetch_assoc($query5)){
   var_dump($result5);
}
cubrid_free_result($query5);

$query6=cubrid_query("insert into query_tb(id,first_name,last_name) values(7,'name7','last7');",$conn);
if (FALSE == $query6) {
    printf("[002]No expect false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    printf("[002]Insert success. [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

$query7=cubrid_query("delete from query_tb where id =1");
if (FALSE == $query7) {
    printf("[003]No expect false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    printf("[003]Delete success. [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}

$query8=cubrid_query("drop table if exists query_tb");
if (FALSE == $query8) {
    printf("[004]No expect false [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}else{
    printf("[004]Drop success. [%d] [%s]\n", cubrid_errno($conn), cubrid_error($conn));
}


cubrid_close($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
name1 
last1
1
array(4) {
  [0]=>
  string(1) "1"
  [1]=>
  string(5) "name1"
  [2]=>
  string(5) "last1"
  [3]=>
  string(7) "COMMENT"
}
array(4) {
  [0]=>
  string(1) "2"
  [1]=>
  string(5) "name2"
  [2]=>
  string(5) "last2"
  [3]=>
  string(7) "COMMENT"
}
array(4) {
  [0]=>
  string(1) "3"
  [1]=>
  string(5) "name3"
  [2]=>
  string(5) "last3"
  [3]=>
  string(7) "COMMENT"
}


#####show statement#####
Array
(
    [Table] => query_tb2
    [Non_unique] => 1
    [Key_name] => index1
    [Seq_in_index] => 1
    [Column_name] => id
    [Collation] => A
    [Cardinality] => 0
    [Sub_part] => 
    [Packed] => 
    [Null] => YES
    [Index_type] => BTREE
    [Func] => 
)
Array
(
    [Table] => query_tb2
    [Non_unique] => 1
    [Key_name] => reverse_index
    [Seq_in_index] => 1
    [Column_name] => address
    [Collation] => D
    [Cardinality] => 0
    [Sub_part] => 
    [Packed] => 
    [Null] => YES
    [Index_type] => BTREE
    [Func] => 
)
Array
(
    [Table] => query_tb2
    [Non_unique] => 0
    [Key_name] => reverse_unique_index
    [Seq_in_index] => 1
    [Column_name] => phone
    [Collation] => D
    [Cardinality] => 0
    [Sub_part] => 
    [Packed] => 
    [Null] => YES
    [Index_type] => BTREE
    [Func] => 
)
Array
(
    [Table] => query_tb2
    [Non_unique] => 0
    [Key_name] => unique_index
    [Seq_in_index] => 1
    [Column_name] => email
    [Collation] => A
    [Cardinality] => 0
    [Sub_part] => 
    [Packed] => 
    [Null] => YES
    [Index_type] => BTREE
    [Func] => 
)


#####describe#####
array(6) {
  ["Field"]=>
  string(2) "id"
  ["Type"]=>
  string(7) "INTEGER"
  ["Null"]=>
  string(3) "YES"
  ["Key"]=>
  string(3) "MUL"
  ["Default"]=>
  NULL
  ["Extra"]=>
  string(0) ""
}
array(6) {
  ["Field"]=>
  string(5) "phone"
  ["Type"]=>
  string(11) "VARCHAR(10)"
  ["Null"]=>
  string(3) "YES"
  ["Key"]=>
  string(3) "UNI"
  ["Default"]=>
  NULL
  ["Extra"]=>
  string(0) ""
}
array(6) {
  ["Field"]=>
  string(7) "address"
  ["Type"]=>
  string(19) "VARCHAR(1073741823)"
  ["Null"]=>
  string(3) "YES"
  ["Key"]=>
  string(3) "MUL"
  ["Default"]=>
  NULL
  ["Extra"]=>
  string(0) ""
}
array(6) {
  ["Field"]=>
  string(5) "email"
  ["Type"]=>
  string(8) "CHAR(30)"
  ["Null"]=>
  string(3) "YES"
  ["Key"]=>
  string(3) "UNI"
  ["Default"]=>
  NULL
  ["Extra"]=>
  string(0) ""
}
array(6) {
  ["Field"]=>
  string(6) "coment"
  ["Type"]=>
  string(19) "VARCHAR(1073741823)"
  ["Null"]=>
  string(3) "YES"
  ["Key"]=>
  string(0) ""
  ["Default"]=>
  NULL
  ["Extra"]=>
  string(0) ""
}


#####explan#####
array(6) {
  ["Field"]=>
  string(7) "comment"
  ["Type"]=>
  string(19) "VARCHAR(1073741823)"
  ["Null"]=>
  string(3) "YES"
  ["Key"]=>
  string(0) ""
  ["Default"]=>
  string(7) "COMMENT"
  ["Extra"]=>
  string(0) ""
}
array(6) {
  ["Field"]=>
  string(2) "id"
  ["Type"]=>
  string(7) "INTEGER"
  ["Null"]=>
  string(2) "NO"
  ["Key"]=>
  string(3) "PRI"
  ["Default"]=>
  NULL
  ["Extra"]=>
  string(0) ""
}
array(6) {
  ["Field"]=>
  string(10) "first_name"
  ["Type"]=>
  string(11) "VARCHAR(10)"
  ["Null"]=>
  string(3) "YES"
  ["Key"]=>
  string(0) ""
  ["Default"]=>
  string(4) "name"
  ["Extra"]=>
  string(0) ""
}
array(6) {
  ["Field"]=>
  string(9) "last_name"
  ["Type"]=>
  string(11) "VARCHAR(20)"
  ["Null"]=>
  string(3) "YES"
  ["Key"]=>
  string(0) ""
  ["Default"]=>
  NULL
  ["Extra"]=>
  string(0) ""
}
[002]Insert success. [0] []
[003]Delete success. [0] []
[004]Drop success. [0] []
Finished!
