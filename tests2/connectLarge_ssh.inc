<?php
$host = "test-db-server";
$port = 33000;
$sshusr = sjkim;
$sshport = 22;
$db = "largedb";
$user = "dba";
$passwd = "";
$connect_url = "CUBRID:$host:$port:$db";
$skip_on_connect_failure  = getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") ? getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") : true;
?>
