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

printf("\n#####negative: cubrid_lob2_tell64, cubrid_lob2_seek64#####\n");
$tell=cubrid_lob2_tell64($lob);
printf("tell: %d\n", $tell);
$size= cubrid_lob2_size64($lob);
printf("size: %d\n", $size);

printf("#####first#####\n");
$seek=cubrid_lob2_seek64($lob,$size, CUBRID_CURSOR_FIRST);
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

//second
printf("#####second#####\n");
cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
$seek=cubrid_lob2_seek64($lob,$size, CUBRID_CURSOR_FIRSTT);
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


//four
printf("#####four#####\n");
cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
cubrid_lob2_seek64($lob,10, CUBRID_CURSOR_FIRST);
$seek=cubrid_lob2_seek64($lob,-11, CUBRID_CURSOR_CURRENT);
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

//five
printf("#####five#####\n");
cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
cubrid_lob2_seek64($lob,10, CUBRID_CURSOR_FIRST);
$seek=cubrid_lob2_seek64($lob,$size, CUBRID_CURSOR_CURRENT);
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

//six
printf("#####six#####\n");
cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
$seek=cubrid_lob2_seek64($lob,1, CUBRID_CURSOR_LAST);
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

//seven
printf("#####seven#####\n");
cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
$seek=cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_LAST);
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

//nine
printf("#####nine#####\n");
cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
$seek=cubrid_lob2_seek64($lob,-66, CUBRID_CURSOR_LAST);
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

//ten
printf("#####ten#####\n");
cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
$seek=cubrid_lob2_seek64($lob,1, CUBRID_CURSOR_CURRENTT);
if($seek === FALSE){
    printf("%d--Error:[%d] [%s]\n",__LINE__,cubrid_error_code(),cubrid_error_msg());
}elseif(is_null($seek)){
    printf("There is maybe some error\n");
}else{
    $tell=cubrid_lob2_tell64($lob);
    if(is_null($tell)){
        printf("There is may be some error\n");
    }else{
        printf("tell: %s\n", $tell);
    }
}

//eleven
printf("#####eleven#####\n");
cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
$seek=cubrid_lob2_seek64($lob,1, CUBRID_CURSOR_FIRSt);
if($seek === FALSE){
    printf("%d--Error:[%d] [%s]\n",__LINE__,cubrid_error_code(),cubrid_error_msg());
}elseif(is_null($seek)){
    printf("There is maybe some error\n");
}else{
    $tell=cubrid_lob2_tell64($lob);
    if(is_null($tell)){
        printf("There is may be some error\n");
    }else{
        printf("tell: %s\n", $tell);
    }
}

//twelve
printf("#####twelve#####\n");
cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
$seek=cubrid_lob2_seek64($lob,1, "1");
if($seek === FALSE){
    printf("%d--Error:[%d] [%s]\n",__LINE__,cubrid_error_code(),cubrid_error_msg());
}elseif(is_null($seek)){
    printf("There is maybe some error\n");
}else{
    $tell=cubrid_lob2_tell64($lob);
    if(is_null($tell)){
        printf("There is may be some error\n");
    }else{
        printf("tell: %s\n", $tell);
    }
}

//thirteen
printf("#####thirteen#####\n");
cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
$seek=cubrid_lob2_seek64($lob,1);
if($seek === FALSE){
    printf("%d--Error:[%d] [%s]\n",__LINE__,cubrid_error_code(),cubrid_error_msg());
}elseif(is_null($seek)){
    printf("There is maybe some error\n");
}else{
    $tell=cubrid_lob2_tell64($lob);
    if(is_null($tell)){
        printf("There is may be some error\n");
    }else{
        printf("tell: %s\n", $tell);
    }
}

//fifteen
printf("#####fifteen#####\n");
cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
$tell=cubrid_lob2_tell64($lo);
if(is_null($tell)){
    printf("There is may be some error\n");
}else{
    printf("tell: %s\n", $tell);
}

//sixteen
printf("#####sixteen#####\n");
cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
$seek=cubrid_lob2_seek64();
if($seek === FALSE){
    printf("%d--Error:[%d] [%s]\n",__LINE__,cubrid_error_code(),cubrid_error_msg());
}elseif(is_null($seek)){
    printf("There is maybe some error\n");
}else{
    $tell=cubrid_lob2_tell64($lob);
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
#####negative: cubrid_lob2_tell64, cubrid_lob2_seek64#####
tell: 0
size: 20000000000
#####first#####
OK
tell: 20000000000
#####second#####

Notice: Use of undefined constant CUBRID_CURSOR_FIRSTT - assumed 'CUBRID_CURSOR_FIRSTT' in %s on line 33

Warning: cubrid_lob2_seek64() expects parameter 3 to be long, string given in %s on line 33
There is maybe some error
tell: 0
#####four#####

Warning: cubrid_lob2_seek64(): offset(-11) is out of range, please input a proper number. in %s on line 53
55--Error:[0] []
tell: 10
#####five#####

Warning: cubrid_lob2_seek64(): offset(20000000000) is out of range, please input a proper number. in %s on line 72
74--Error:[0] []
tell: 10
#####six#####
OK
tell: 19999999999
#####seven#####
OK
tell: 20000000000
#####nine#####

Warning: cubrid_lob2_seek64(): offset(-66) may be a negative number or out of range, please check the offset you give. in %s on line 126
128--Error:[0] []
tell: 0
#####ten#####

Notice: Use of undefined constant CUBRID_CURSOR_CURRENTT - assumed 'CUBRID_CURSOR_CURRENTT' in %s on line 144

Warning: cubrid_lob2_seek64() expects parameter 3 to be long, string given in %s on line 144
There is maybe some error
#####eleven#####

Notice: Use of undefined constant CUBRID_CURSOR_FIRSt - assumed 'CUBRID_CURSOR_FIRSt' in %s on line 161

Warning: cubrid_lob2_seek64() expects parameter 3 to be long, string given in %s on line 161
There is maybe some error
#####twelve#####
tell: 1
#####thirteen#####
tell: 1
#####fifteen#####

Notice: Undefined variable: lo in %s on line 212

Warning: cubrid_lob2_tell64() expects parameter 1 to be resource, null given in %s on line 212
There is may be some error
#####sixteen#####

Warning: cubrid_lob2_seek64() expects at least 2 parameters, 0 given in %s on line 222
There is maybe some error
Finished!
