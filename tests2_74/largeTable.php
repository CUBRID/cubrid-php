<?php
require_once 'connectLarge.inc';
require_once 'until.php';

if (!$conn = cubrid_connect($host, $port, $db,  $user, $passwd)) {
    printf("Cannot connect to db server using host=%s, port=%d, dbname=%s, user=%s, passwd=***\n", $host, $port, $db, $user);
    exit(1);
}

$retval = check_table_existence($conn, "largetable");
if($retval == -1) {
    exit(1);
}elseif($retval == 1) {
    printf("this table is created\n");
}else{
    printf("#####start: create largetable#####\n");
    $cubrid_req = cubrid_execute($conn, "CREATE TABLE largetable(a int AUTO_INCREMENT, b clob)");
    if (!$cubrid_req) {
        printf("Failed to create test table: [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
        exit(1);
    }
    
    $req = cubrid_prepare($conn, "insert into largetable(b) values (?)");
    $importName=array("largeFile/large.txt");
    for($i=0; $i<count($importName); $i++){
        $lob=cubrid_lob2_new($conn, "CLOB");
        cubrid_lob2_import($lob, $importName[$i]);
        cubrid_lob2_bind($req, 1 , $lob, "CLOB");
        cubrid_execute($req);
        cubrid_lob2_close($lob);
    }
    cubrid_close_prepare($req);
    
    if (!cubrid_commit($conn)) {
        exit(1);
    }
}

?>

