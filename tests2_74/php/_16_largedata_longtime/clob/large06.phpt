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


$tell=cubrid_lob2_tell($lob);
printf("tell: %d\n", $tell);
$size= cubrid_lob2_size64($lob);
printf("lob size: %s\n", $size);

printf("#####first#####\n");
cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
$seek=cubrid_lob2_seek64($lob,-1, CUBRID_CURSOR_LAST);
if($seek === FALSE){
    printf("%d--Error:[%d] [%s]\n",__LINE__,cubrid_error_code(),cubrid_error_msg());
}elseif(is_null($seek)){
    printf("There is maybe some error\n");
}else{
    printf("cubrid_lob2_seek64(\$lob,-1, CUBRID_CURSOR_LAST)  OK\n");
}
$tell=cubrid_lob2_tell64($lob);
if(is_null($tell)){
    printf("There is may be some error\n");
}else{
    printf("tell: %s\n", $tell);
}

printf("#####second#####\n");
cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
$seek64=cubrid_lob2_seek64($lob,"1a", CUBRID_CURSOR_CURRENT);
if($seek64 === FALSE){
    printf("%d--Error:[%d] [%s]\n",__LINE__,cubrid_error_code(),cubrid_error_msg());
}elseif(is_null($seek64)){
    printf("There is maybe some error\n");
}else{
    $tell64=cubrid_lob2_tell64($lob);
    if(is_null($tell64)){
        printf("There is may be some error\n");
    }else{
        printf("tell: %s\n", $tell64);
    }
}

printf("#####third#####\n");
cubrid_lob2_seek($lob,0, CUBRID_CURSOR_FIRST);
$seek=cubrid_lob2_seek($lob,"1a", CUBRID_CURSOR_CURRENT);
if($seek === FALSE){
    printf("%d--Error:[%d] [%s]\n",__LINE__,cubrid_error_code(),cubrid_error_msg());
}elseif(is_null($seek)){
    printf("There is maybe some error\n");
}else{
    $tell=cubrid_lob2_tell($lob);
    if(is_null($tell)){
        printf("There is may be some error\n");
    }else{
        printf("tell: %s\n", $tell);
    }
}

cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
tell: 0
lob size: 20000000000
#####first#####

Warning: cubrid_lob2_seek64(): offset(-1) must not be a negative number, so please check the offset you give. in %s on line 16
18--Error:[0] []
tell: 0
#####second#####
tell: 1
#####third#####

Notice: A non well formed numeric value encountered in %s on line 49
tell: 1
Finished!
