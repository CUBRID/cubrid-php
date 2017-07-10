--TEST--
negative: cubrid_lob2_read
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

printf("\n#####negative: cubrid_lob2_read#####\n");
printf("#####first#####\n");
cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
printf("tell: %d\n", cubrid_lob2_tell64($lob));
$size=cubrid_lob2_size64($lob);
printf("%d--lob size: %f\n", __LINE__, $size);


cubrid_lob2_seek64($lob,3, CUBRID_CURSOR_LAST);
printf("tell: %s\n", cubrid_lob2_tell64($lob));
$data= cubrid_lob2_read($lob,20);
if($data === FALSE){
    printf("There is no more data\n");
}elseif(is_null($data)){
    printf("There must be some errors\n");
}else{
    printf("%d--data: %s\n", __LINE__, $data);
}
$tell=cubrid_lob2_tell64($lob);
if($tell === FALSE){
    printf("There is no more data\n");
}else{
    printf("tell: %s\n", $tell);
}

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####negative: cubrid_lob2_read#####
#####first#####
tell: 0
13--lob size: 20000000000.000000
tell: 19999999997
24--data: aaa
tell: 20000000000
Finished!
