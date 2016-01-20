--TEST--
negative: cubrid_lob2_tell64, cubrid_lob2_seek64
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connectLarge.inc";
$conn=cubrid_connect($host, $port, $db, $user, $passwd);
$req=cubrid_execute($conn, "select * from largeTable");
$row = cubrid_fetch($req, CUBRID_NUM | CUBRID_LOB);
$lob=$row[1];

printf("\n#####negative: cubrid_lob2_tell, cubrid_lob2_seek#####\n");
$tell=cubrid_lob2_tell($lob);
printf("tell: %d\n", $tell);
$size= cubrid_lob2_size($lob);
printf("size: %d\n", $size);

printf("#####cubrid_lob2_seek, cubrid_lob2_tell#####\n");
cubrid_lob2_seek($lob,0, CUBRID_CURSOR_FIRST);
$seek=cubrid_lob2_seek($lob,-1, CUBRID_CURSOR_FIRST);
if($seek === FALSE){
    printf("%d--Error:[%d] [%s]\n",__LINE__,cubrid_error_code(),cubrid_error_msg());
}elseif(is_null($seek)){
    printf("There is maybe some error\n");
}else{
    printf("OK\n");
}
$tell=cubrid_lob2_tell($lob);
if(is_null($tell)){
    printf("There is may be some error\n");
}else{
    printf("tell: %s\n", $tell);
}

printf("#####cubrid_lob2_tell64, cubrid_lob2_seek64#####\n");
cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
$seek64=cubrid_lob2_seek64($lob,-1, CUBRID_CURSOR_FIRST);
if($seek64 === FALSE){
    printf("%d--Error:[%d] [%s]\n",__LINE__,cubrid_error_code(),cubrid_error_msg());
}elseif(is_null($seek64)){
    printf("There is maybe some error\n");
}else{
    printf("OK\n");
}
$tell64=cubrid_lob2_tell64($lob);
if(is_null($tell64)){
    printf("There is may be some error\n");
}else{
    printf("tell: %s\n", $tell64);
}

cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####negative: cubrid_lob2_tell, cubrid_lob2_seek#####
tell: 0
size: 20000000000
#####cubrid_lob2_seek, cubrid_lob2_tell#####

Warning: cubrid_lob2_seek(): offset(-1) is not correct, it can't be a negative number or larger than size in %s on line 16
18--Error:[0] []
tell: 0
#####cubrid_lob2_tell64, cubrid_lob2_seek64#####

Warning: cubrid_lob2_seek64(): offset(-1) may be a negative number or out of range, please check the offset you give. in %s on line 33
35--Error:[0] []
tell: 0
Finished!

