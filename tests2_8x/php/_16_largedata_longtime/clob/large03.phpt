--TEST--
negative: cubrid_lob2_seek64 
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connectLarge.inc");
$conn=cubrid_connect($host, $port, $db, $user, $passwd);
$req=cubrid_execute($conn, "select * from largeTable");
$row = cubrid_fetch($req, CUBRID_NUM | CUBRID_LOB);
$lob=$row[1];

printf("\n#####negative: cubrid_lob2_tell64, cubrid_lob2_seek64#####\n");
$tell=cubrid_lob2_tell64($lob);
printf("tell: %d\n", $tell);
$size= cubrid_lob2_size64($lob);
printf("size: %s\n", $size);

printf("#####first#####\n");
$seek=cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
$seek=cubrid_lob2_seek64($lob,$size+20, CUBRID_CURSOR_FIRST);
if($seek === FALSE){
    printf("%d--Error:[%d] [%s]\n",__LINE__,cubrid_error_code(),cubrid_error_msg());
}elseif(is_null($seek)){
    printf("There is maybe some error\n");
}else{
    printf("OK\n");
}
$tell=cubrid_lob2_tell64($lob);
if(is_null($tell)){
    printf("There is may be some error\n");
}else{
    printf("tell: %s\n", $tell);
}

$data= cubrid_lob2_read($lob,3);
if($data === FALSE){
    printf("There is no more data\n");
}elseif(is_null($data)){
    printf("There must be some errors\n");
}else{
    printf("data: %s\n", $data);
}

cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####negative: cubrid_lob2_tell64, cubrid_lob2_seek64#####
tell: 0
size: 20000000000
#####first#####

Warning: cubrid_lob2_seek64(): offset(20000000020) is out of range for the lob field you have chosen, so please check the offset you give and the lob length. in %s on line 16
18--Error:[0] []
tell: 0
data: aaa
Finished!
