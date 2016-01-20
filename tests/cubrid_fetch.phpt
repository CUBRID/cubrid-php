--TEST--
cubrid_fetch
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');

$tmp = NULL;
$conn = NULL;

if (!is_null($tmp = @cubrid_fetch_array()))
{
    printf ("[001] Expecting NULL, got %s/%s\n", gettype($tmp), $tmp);
}

if (!is_null($tmp = @cubrid_fetch($conn)))
{
    printf ("[002] Expecting NULL, got %s/%s\n", gettype($tmp), $tmp);
}

$conn = cubrid_connect_with_url($connect_url);

if (!$req = cubrid_execute($conn, "SELECT * FROM code"))
{
    printf ("[003] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
    exit(1);
}

print_r(cubrid_fetch($req, CUBRID_NUM));

print_r(cubrid_fetch($req, CUBRID_ASSOC));

print_r(cubrid_fetch($req));

print_r(cubrid_fetch($req, CUBRID_OBJECT));

print "done!";
?>
--CLEAN--
--EXPECTF--
Array
(
    [0] => X
    [1] => Mixed
)
Array
(
    [s_name] => W
    [f_name] => Woman
)
Array
(
    [0] => M
    [s_name] => M
    [1] => Man
    [f_name] => Man
)
stdClass Object
(
    [s_name] => B
    [f_name] => Bronze
)
done!
