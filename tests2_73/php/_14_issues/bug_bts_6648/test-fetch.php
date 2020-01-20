<?php 
$sql = "select * from foo"; 

for ($i = 0; $i <1;$i++) { 
    $con = cubrid_connect ("test-db-server", 33113, "testdb", "dba",""); 
    // $con = cubrid_connect ("10.24.18.65", 33099, "ccitest", "dba", "") 

    if ($con) { 
            $req = cubrid_query ($sql, $con); 
            //$req = cubrid_execute ($con, $sql) 
            if (! $req) { 
                break; 
            } 

            while ($row = cubrid_fetch ($req)) { 
              echo "in $row[0], b: $row[1] \n "; 
            } 

            if ($req) { 
                cubrid_close_request ($req); 
            } 

        cubrid_disconnect ($con); 
    } else { 
        echo "failed cubrid_connect. \n"; 
        sleep (1); 
    } 
} 
?>
