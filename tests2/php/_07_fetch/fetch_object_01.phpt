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

if (is_null($tmp = @cubrid_fetch_object())) {
    printf("[001] Expecting NULL, got %s,%s\n", gettype($tmp), $tmp);
}
if (is_null($tmp2 = @cubrid_fetch_object($conn))) {
    printf("[002] Expecting NULL, got %s,%s\n", gettype($tmp2), $tmp2);
}

$conn = cubrid_connect($host, $port, $db, $user, $passwd);

cubrid_execute($conn,"drop table if exists fetch_object_tb");
cubrid_execute($conn,"CREATE TABLE fetch_object_tb(c1 string, c2 char(20), c3 int, c4 double, c5 time, c6 date)");
cubrid_execute($conn,"insert into fetch_object_tb values('string1','char11111',1,11.11,TIME '02:10:22',DATE '08/14/1977')");
cubrid_execute($conn,"insert into fetch_object_tb values('string2','char2',2,222222,TIME '1:15',DATE '00-10-31')");
cubrid_execute($conn,"insert into fetch_object_tb values('string4','char4',4,44,TIME '4:15',DATE '04-10-21')");
cubrid_execute($conn,"insert into fetch_object_tb values('string3','char3',3,33,TIME '3:15',DATE '03-10-31')");
cubrid_execute($conn,"insert into fetch_object_tb values('string5','char5',5,55,TIME '5:15',DATE '05-10-01')");
cubrid_execute($conn,"insert into fetch_object_tb values('string6','char6',6,66,TIME '6:15',DATE '06-10-11')");
cubrid_execute($conn,"insert into fetch_object_tb values('string7','char7',7,77,TIME '7:15',DATE '07-10-11')");
cubrid_execute($conn,"insert into fetch_object_tb values('string8','char8',8,88,TIME '8:15',DATE '08-10-11')");
cubrid_execute($conn,"insert into fetch_object_tb values('string9','char9',9,99,TIME '9:15',DATE '09-10-11')");	
cubrid_execute($conn,"insert into fetch_object_tb values('string10','char10',10,1010,TIME '10:15',DATE '010-10-11')");
$res = cubrid_execute($conn, "SELECT * FROM fetch_object_tb order by c1");
if (!$res){ 
    printf("[003] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
    exit(1);
}
printf("cubrid_fetch_object(res) start\n");
var_dump(cubrid_fetch_object($res));

class cubrid_fetch_object_test {
    public $c1 = NULL;
    public $c2 = NULL;

    public function toString() {
        var_dump($this);
    }
}

var_dump(cubrid_fetch_object($res, 'cubrid_fetch_object_test'));
class cubrid_fetch_object_construct extends cubrid_fetch_object_test {
	public function __construct($s, $f) {
		$this->c1 = $s;
		$this->c2 = $f;
	}
}
printf("cubrid_fetch_object(res, string ,array) cubrid_fetch_object_construct start1\n");
var_dump(cubrid_fetch_object($res, 'cubrid_fetch_object_construct', null));
printf("start2:\n");
var_dump(cubrid_fetch_object($res, 'cubrid_fetch_object_construct', array('c1')));
printf("start3:\n");
var_dump(cubrid_fetch_object($res, 'cubrid_fetch_object_construct', array('c1', 'c2')));
printf("start4:\n");
var_dump(cubrid_fetch_object($res, 'cubrid_fetch_object_construct', array('c1', 'c2', 'c3')));
class cubrid_fetch_object_private_construct {
	private function __construct($s, $f) {
		var_dump($s);
	}
}
printf("cubrid_fetch_object(res, string ,array) cubrid_fetch_object_private_construct start1\n");
var_dump(cubrid_fetch_object($res, 'cubrid_fetch_object_private_construct', array('c1', 'c2')));


printf("start5:\n");
var_dump(cubrid_fetch_object($res));
printf("start6:\n");
var_dump(cubrid_fetch_object($res, 'cubrid_fetch_object_construct', array('c1', 'c2')));
// Fatal error, script execution will end
printf("start6:\n");
var_dump(cubrid_fetch_object($res, 'this_class_does_not_exist'));

cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
[001] Expecting NULL, got NULL,
[002] Expecting NULL, got NULL,
cubrid_fetch_object(res) start
object(stdClass)#1 (6) {
  ["c1"]=>
  string(7) "string1"
  ["c2"]=>
  string(20) "char11111           "
  ["c3"]=>
  string(1) "1"
  ["c4"]=>
  string(19) "11.109999999999999%d"
  ["c5"]=>
  string(8) "02:10:22"
  ["c6"]=>
  string(10) "1977-08-14"
}
object(cubrid_fetch_object_test)#1 (6) {
  ["c1"]=>
  string(8) "string10"
  ["c2"]=>
  string(20) "char10              "
  ["c3"]=>
  string(2) "10"
  ["c4"]=>
  string(21) "1010.0000000000000000"
  ["c5"]=>
  string(8) "10:15:00"
  ["c6"]=>
  string(10) "0010-10-11"
}
cubrid_fetch_object(res, string ,array) cubrid_fetch_object_construct start1

Warning: Missing argument 1 for cubrid_fetch_object_construct::__construct() in %s on line %d

Warning: Missing argument 2 for cubrid_fetch_object_construct::__construct() in %s on line %d

Notice: Undefined variable: s in %s on line %d

Notice: Undefined variable: f in %s on line %d
object(cubrid_fetch_object_construct)#1 (6) {
  ["c1"]=>
  NULL
  ["c2"]=>
  NULL
  ["c3"]=>
  string(1) "2"
  ["c4"]=>
  string(23) "222222.0000000000000000"
  ["c5"]=>
  string(8) "01:15:00"
  ["c6"]=>
  string(10) "2000-10-31"
}
start2:

Warning: Missing argument 2 for cubrid_fetch_object_construct::__construct() in %s on line %d

Notice: Undefined variable: f in %s on line %d
object(cubrid_fetch_object_construct)#1 (6) {
  ["c1"]=>
  string(2) "c1"
  ["c2"]=>
  NULL
  ["c3"]=>
  string(1) "3"
  ["c4"]=>
  string(19) "33.0000000000000000"
  ["c5"]=>
  string(8) "03:15:00"
  ["c6"]=>
  string(10) "2003-10-31"
}
start3:
object(cubrid_fetch_object_construct)#1 (6) {
  ["c1"]=>
  string(2) "c1"
  ["c2"]=>
  string(2) "c2"
  ["c3"]=>
  string(1) "4"
  ["c4"]=>
  string(19) "44.0000000000000000"
  ["c5"]=>
  string(8) "04:15:00"
  ["c6"]=>
  string(10) "2004-10-21"
}
start4:
object(cubrid_fetch_object_construct)#1 (6) {
  ["c1"]=>
  string(2) "c1"
  ["c2"]=>
  string(2) "c2"
  ["c3"]=>
  string(1) "5"
  ["c4"]=>
  string(19) "55.0000000000000000"
  ["c5"]=>
  string(8) "05:15:00"
  ["c6"]=>
  string(10) "2005-10-01"
}
cubrid_fetch_object(res, string ,array) cubrid_fetch_object_private_construct start1
string(2) "c1"
object(cubrid_fetch_object_private_construct)#1 (6) {
  ["c1"]=>
  string(7) "string6"
  ["c2"]=>
  string(20) "char6               "
  ["c3"]=>
  string(1) "6"
  ["c4"]=>
  string(19) "66.0000000000000000"
  ["c5"]=>
  string(8) "06:15:00"
  ["c6"]=>
  string(10) "2006-10-11"
}
start5:
object(stdClass)#1 (6) {
  ["c1"]=>
  string(7) "string7"
  ["c2"]=>
  string(20) "char7               "
  ["c3"]=>
  string(1) "7"
  ["c4"]=>
  string(19) "77.0000000000000000"
  ["c5"]=>
  string(8) "07:15:00"
  ["c6"]=>
  string(10) "2007-10-11"
}
start6:
object(cubrid_fetch_object_construct)#1 (6) {
  ["c1"]=>
  string(2) "c1"
  ["c2"]=>
  string(2) "c2"
  ["c3"]=>
  string(1) "8"
  ["c4"]=>
  string(19) "88.0000000000000000"
  ["c5"]=>
  string(8) "08:15:00"
  ["c6"]=>
  string(10) "2008-10-11"
}
start6:

Fatal error: Class 'this_class_does_not_exist' not found in %s on line %d
