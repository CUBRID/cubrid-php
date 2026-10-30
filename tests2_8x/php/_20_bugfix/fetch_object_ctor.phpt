--TEST--
cubrid_fetch_object: NULL ctor_params for a class without constructor, ctor_params passed by position
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include "connect.inc";
$conn = cubrid_connect($host, $port, $db, $user, $passwd);
class NoCtor {
    public $id;
}

class WithCtor {
    public $id;
    public $args;
    function __construct($first, $second) {
        $this->args = array($first, $second);
    }
}

$req = cubrid_execute($conn, "select 1 as id from db_root union all select 2 from db_root union all select 3 from db_root");

// NULL means "no ctor_params": must not throw "does not have a constructor".
try {
    var_dump(cubrid_fetch_object($req, "NoCtor", NULL));
} catch (Exception $e) {
    echo get_class($e), ": ", $e->getMessage(), "\n";
}

// Array keys are ignored and values are passed in order (PHP 7 behavior).
var_dump(cubrid_fetch_object($req, "WithCtor", array("b" => "B", "a" => "A")));

// A class without constructor and non-empty ctor_params is still an error.
try {
    var_dump(cubrid_fetch_object($req, "NoCtor", array(1)));
} catch (Exception $e) {
    echo get_class($e), ": ", $e->getMessage(), "\n";
}

cubrid_close_request($req);
cubrid_disconnect($conn);
print "done!\n";
?>
--CLEAN--
--EXPECTF--
object(NoCtor)#%d (1) {
  ["id"]=>
  string(1) "1"
}
object(WithCtor)#%d (2) {
  ["id"]=>
  string(1) "2"
  ["args"]=>
  array(2) {
    [0]=>
    string(1) "B"
    [1]=>
    string(1) "A"
  }
}
Exception: Class NoCtor does not have a constructor hence you cannot use ctor_params
done!
