<?php
$host = getenv("CUBRID_TEST_HOST") ? getenv("CUBRID_TEST_HOST") : "test-db-server";
$port = 33000;
$db = "demodb";
$user = "public";
$passwd = "";
$connect_url = "CUBRID:$host:$port:$db:::";
$skip_on_connect_failure  = getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") ? getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") : true;
?>
