<?php 
$sql = "insert into foo values(1,1)"; 

for ($i = 0; $i <1; $i++) { 
    $con = cubrid_connect("test-db-server", 33113, "testdb", "dba", "");

    if ($con) { 
            $req = cubrid_query($sql, $con); 
            if ($req) { 
                echo "cubrid_query error"; 
            } 

            while ($row = cubrid_fetch($req )) { 
              echo "to: $row[0], b: $row[1] \n"; 
            } 

            if ($req) { 
                cubrid_close_request($req); 
            } 
        cubrid_disconnect($con); 
    } else { 
        echo "cubrid_connect failed. \n "; 
        sleep (1); 
    } 
} 
?>
