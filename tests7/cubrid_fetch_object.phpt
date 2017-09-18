--TEST--
cubrid_fetch_object
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

if (!is_null($tmp = @cubrid_fetch_object())) {
    printf('[001] Expecting NULL, got %s/%s\n', gettype($tmp), $tmp);
}

if (!is_null($tmp = @cubrid_fetch_object($conn))) {
    printf('[002] Expecting NULL, got %s/%s\n', gettype($tmp), $tmp);
}

$conn = cubrid_connect($host, $port, $db, $user, $passwd);

if (!($res = cubrid_execute($conn, "SELECT * FROM code limit 5"))) {
    printf('[003] [%d] %s\n', cubrid_error_code(), cubrid_error_msg());
    exit(1);
}

var_dump(cubrid_fetch_object($res));

class cubrid_fetch_object_test {
    public $s_name = NULL;
    public $f_name = NULL;

    public function toString() {
        var_dump($this);
    }
}

var_dump(cubrid_fetch_object($res, 'cubrid_fetch_object_test'));

class cubrid_fetch_object_test_construct extends cubrid_fetch_object_test {
	public function __construct($s, $f) {
		try 
		{
			$this->s_name = $s;
			$this->f_name = $f;
		}
		catch(Throwable $t) 
		{
		echo $t->getMessage();
		}
		catch(Exception $e) 
		{
		echo $e;
		}

	}
}

//var_dump(cubrid_fetch_object($res, 'cubrid_fetch_object_test_construct', null));
//var_dump(cubrid_fetch_object($res, 'cubrid_fetch_object_test_construct', array('s_name')));
var_dump(cubrid_fetch_object($res, 'cubrid_fetch_object_test_construct', array('s_name', 'f_name')));
var_dump(cubrid_fetch_object($res, 'cubrid_fetch_object_test_construct', array('s_name', 'f_name', 'x')));
//var_dump(cubrid_fetch_object($res, 'cubrid_fetch_object_test_construct', "no array and not null"));
var_dump(cubrid_fetch_object($res));
var_dump(cubrid_fetch_object($res, 'cubrid_fetch_object_test_construct', array('s_name', 'f_name')));

class cubrid_fetch_object_private_construct {

	private function __construct($s, $f) {
		var_dump($s);
	}

}

var_dump(cubrid_fetch_object($res, 'cubrid_fetch_object_private_construct', array('s_name', 'f_name')));

// Fatal error, script execution will end
//var_dump(cubrid_fetch_object($res, 'this_class_does_not_exist'));

cubrid_disconnect($conn);

print "done!";
?>
--CLEAN--
--EXPECTF--
object(stdClass)#1 (2) {
  ["s_name"]=>
  string(1) "X"
  ["f_name"]=>
  string(5) "Mixed"
}
object(cubrid_fetch_object_test)#1 (2) {
  ["s_name"]=>
  string(1) "W"
  ["f_name"]=>
  string(5) "Woman"
}
object(cubrid_fetch_object_test_construct)#1 (2) {
  ["s_name"]=>
  string(6) "s_name"
  ["f_name"]=>
  string(6) "f_name"
}
object(cubrid_fetch_object_test_construct)#1 (2) {
  ["s_name"]=>
  string(6) "s_name"
  ["f_name"]=>
  string(6) "f_name"
}
object(stdClass)#1 (2) {
  ["s_name"]=>
  string(1) "S"
  ["f_name"]=>
  string(6) "Silver"
}
bool(false)
bool(false)
done!

