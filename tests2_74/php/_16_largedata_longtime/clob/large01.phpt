--TEST--
positive: cubrid_lob2_tell6464, cubrid_lob2_size6464, cubrid_lob_seek64
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connectLarge.inc";

// set memory_limit to 512M for this test;
ini_set('memory_limit','512M');

//cubrid_lob2_read
printf("\n#####cubrid_lob2_read#####\n");
$conn=cubrid_connect($host, $port, $db, $user, $passwd);
$req=cubrid_execute($conn, "select * from largeTable");

$row = cubrid_fetch($req, CUBRID_NUM | CUBRID_LOB);
$lob=$row[1];

$tell=cubrid_lob2_tell64($lob);
if($tell === FALSE){
    printf("%d--There is no more data\n", __LINE__);
}elseif(is_null($tell)){
    printf("%d--there is maybe some error\n", __LINE__);
}else{
    printf("%d--tell: %s\n", __LINE__, $tell);
    printf("%d--tell: %f\n", __LINE__, $tell);
}

$size= cubrid_lob2_size64($lob);
if($size === FALSE){
    printf("%d--Error:[%d] [%s]\n",__LINE__,cubrid_error_code(),cubrid_error_msg());
}elseif(is_null($size)){
    printf("%d--there is maybe some error\n", __LINE__);
}else{
    printf("%d--size: %s\n", __LINE__, $size);
    printf("%d--size: %f\n", __LINE__, $size);
}

$data= cubrid_lob2_read($lob, 100000000);
if($data === FALSE){
    printf("%d--Error:[%d] [%s]\n",__LINE__,cubrid_error_code(),cubrid_error_msg());
}elseif(is_null($data)){
    printf("%d--there is maybe some error\n", __LINE__);
}else{
    printf("%d--read data ok\n", __LINE__);
}

//cubrid_lob2_tell64
$tell=cubrid_lob2_tell64($lob);
if($tell === FALSE){
    printf("%d--There is no more data\n", __LINE__);
}elseif(is_null($tell)){
    printf("%d--there is maybe some error\n", __LINE__);
}else{
    printf("%d--tell: %s\n", __LINE__, $tell);
    printf("%d--tell: %f\n", __LINE__, $tell);
}


//cubrid_lob2_seek64
$seek=cubrid_lob2_seek64($lob, 2, CUBRID_CURSOR_CURRENT);
if($seek === FALSE){
    printf("%d--There is no more data\n", __LINE__);
}elseif(is_null($seek)){
    printf("%d--there is maybe some error\n", __LINE__);
}else{
    printf("%d--ok\n", __LINE__);
    printf("%d--tell: %s\n", __LINE__, cubrid_lob2_tell64($lob));
    printf("%d--tell: %f\n", __LINE__, cubrid_lob2_tell64($lob));
}

cubrid_disconnect($conn);
print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####cubrid_lob2_read#####
21--tell: 0
22--tell: 0.000000
31--size: 20000000000
32--size: 20000000000.000000
41--read data ok
51--tell: 100000000
52--tell: 100000000.000000
63--ok
64--tell: 100000002
65--tell: 100000002.000000
Finished!
