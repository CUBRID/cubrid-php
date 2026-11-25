<?php
$host = "10.34.64.61";
$port1 = 33199;
$port2 = 30199;
$db = "db_9277";
$user = "dba";
$passwd = "";
$connect_url = "CUBRID:$host:$port1:$db:dba::?althosts=$host:$port2&rctime=30";
$skip_on_connect_failure  = getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") ? getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") : true;
?>
