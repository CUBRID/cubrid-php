<?php
$host = "127.0.0.1";
$port = 33000;
$db = "demodb";
$user = "PUBLIC";
$passwd = "";
$connect_url = "cci:CUBRID:127.0.0.1:33000:demodb:public::";
$skip_on_connect_failure  = getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") ? getenv("CUBRID_TEST_SKIP_CONNECT_FAILURE") : true;
?>
