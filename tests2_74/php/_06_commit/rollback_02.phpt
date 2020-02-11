--TEST--
cubrid_rollback
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once("connect.inc");

$conn = cubrid_connect_with_url($connect_url, $user, $passwd);
cubrid_execute($conn, "DROP TABLE if exists roll2_tb");
$sql = <<<EOD
CREATE TABLE roll2_tb(
pub_id CHAR(3), 
pub_name VARCHAR(20), 
city VARCHAR(15), 
state CHAR(2), 
country VARCHAR(15)
)
EOD;

if (!cubrid_execute($conn, $sql)) {
    printf("Error facility: [%d]\nError code: [%d]\nError msg: [%s]\n", cubrid_error_code_facility(), cubrid_error_code(), cubrid_error_msg());
    cubrid_disconnect($conn);
    exit;
}

$req = cubrid_prepare($conn, "INSERT INTO roll2_tb VALUES(?, ?, ?, ?, ?)");

$id_list = array("P01", "P02", "P03", "P04");
$name_list = array("Abatis Publishers", "Core Dump Books", "Schadenfreude Press", "Tenterhooks Press");
$city_list = array("New York", "San Francisco", "Hamburg", "Berkeley");
$state_list = array("NY", "CA", NULL, "CA");
$country_list = array("USA", "USA", "Germany", "USA");

for ($i = 0, $size = count($id_list); $i < $size; $i++) {
    cubrid_bind($req, 1, $id_list[$i]);
    cubrid_bind($req, 2, $name_list[$i]);
    cubrid_bind($req, 3, $city_list[$i]);
    cubrid_bind($req, 4, $state_list[$i]);
    cubrid_bind($req, 5, $country_list[$i]);

    if (!($ret = cubrid_execute($req))) {
        break;
    }
}

if (!$ret) {
    cubrid_rollback($conn);
} else {
    cubrid_commit($conn);

    $req = cubrid_execute($conn, "SELECT * FROM roll2_tb");
    while ($result = cubrid_fetch_assoc($req)) {
        printf("%-3s %-20s %-15s %-3s %-15s\n", 
            $result["pub_id"], $result["pub_name"], $result["city"], $result["state"], $result["country"]);
    }
}

cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
P01 Abatis Publishers    New York        NY  USA            
P02 Core Dump Books      San Francisco   CA  USA            
P03 Schadenfreude Press  Hamburg             Germany        
P04 Tenterhooks Press    Berkeley        CA  USA            
Finished!
