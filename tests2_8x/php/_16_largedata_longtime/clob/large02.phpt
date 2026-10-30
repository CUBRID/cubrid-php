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
printf("size: %s\n", $size);

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
try {
    $seek=cubrid_lob2_seek64($lob,$size, 'CUBRID_CURSOR_FIRSTT');
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $seek = NULL;
}
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
try {
    $seek=cubrid_lob2_seek64($lob,1, 'CUBRID_CURSOR_CURRENTT');
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $seek = NULL;
}
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
try {
    $seek=cubrid_lob2_seek64($lob,1, 'CUBRID_CURSOR_FIRSt');
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $seek = NULL;
}
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
try {
    $tell=cubrid_lob2_tell64($lo);
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $tell = NULL;
}
if(is_null($tell)){
    printf("There is may be some error\n");
}else{
    printf("tell: %s\n", $tell);
}

//sixteen
printf("#####sixteen#####\n");
cubrid_lob2_seek64($lob,0, CUBRID_CURSOR_FIRST);
try {
    $seek=cubrid_lob2_seek64();
} catch (ArgumentCountError | TypeError $e) {
    echo $e->getMessage(), "\n";
    $seek = NULL;
}
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
cubrid_lob2_seek64(): Argument #3 ($origin) must be of type int, string given
There is maybe some error
tell: 0
#####four#####

Warning: cubrid_lob2_seek64(): offset(-11) is out of range for the lob field you have chosen, so please check the offset you give and the lob length. in %s on line 58
60--Error:[0] []
tell: 10
#####five#####

Warning: cubrid_lob2_seek64(): offset(20000000000) is out of range for the lob field you have chosen, so please check the offset you give and the lob length. in %s on line 77
79--Error:[0] []
tell: 10
#####six#####
OK
tell: 19999999999
#####seven#####
OK
tell: 20000000000
#####nine#####

Warning: cubrid_lob2_seek64(): offset(-66) must not be a negative number, so please check the offset you give. in %s on line 131
133--Error:[0] []
tell: 0
#####ten#####
cubrid_lob2_seek64(): Argument #3 ($origin) must be of type int, string given
There is maybe some error
#####eleven#####
cubrid_lob2_seek64(): Argument #3 ($origin) must be of type int, string given
There is maybe some error
#####twelve#####
tell: 1
#####thirteen#####
tell: 1
#####fifteen#####

Warning: Undefined variable $lo in %s on line 228
cubrid_lob2_tell64(): Argument #1 ($lob2_id) must be of type resource, null given
There is may be some error
#####sixteen#####
cubrid_lob2_seek64() expects at least 2 arguments, 0 given
There is maybe some error
Finished!
