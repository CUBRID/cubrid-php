<?php
include "connect.inc";
header('Content-type: text/html; charset=euc-kr');

$conn = cubrid_connect($host, $port, $db,"dba", $passwd);
$sql = "insert into cubridsus9278(b) values('aaaa')";
$i = 0;
while ($i<300){
    $result=cubrid_execute($conn, $sql);
    if ($result == true)
     { cubrid_commit($conn); }
    else
     { cubrid_rollback($conn); }

    $i++;
}

?>
