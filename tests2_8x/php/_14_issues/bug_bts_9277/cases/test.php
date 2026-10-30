<?php
include_once "connect.inc";
header('Content-type: text/html; charset=euc-kr');

//var_dump($connect_url);
$conn = cubrid_connect_with_url($connect_url);
$sql = "SELECT a from foo";

$result=cubrid_execute($conn, $sql);
$pResult = cubrid_fetch($result);
echo($pResult[0]);
echo("<br/>");
cubrid_close_request($result);
cubrid_rollback($conn);
cubrid_disconnect($conn);
?>
