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
