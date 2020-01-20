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
$res = cubrid_execute($conn, "SELECT * FROM fetch_object_tb where c3 > 10");
if (!$res){ 
    printf("[003] [%d] %s\n", cubrid_error_code(), cubrid_error_msg());
    exit(1);
}
printf("cubrid_fetch_object(res) start\n");
$result=cubrid_fetch_object($res);
if(false == $result){
   printf("[001] Expecting FALSE, got %s,%s\n", gettype($result), $result);
}else{
   var_dump($result);
}

class cubrid_fetch_object_test {
    public $c1 = NULL;
    public $c2 = NULL;

    public function toString() {
        var_dump($this);
    }
}

$result2=cubrid_fetch_object($res, 'cubrid_fetch_object_test');
if(false == $result2){
   printf("[002] Expecting FALSE, got %s,%s\n", gettype($result2), $result2);
}else{
   var_dump($result2);
}

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
[001] Expecting FALSE, got boolean,
[002] Expecting FALSE, got boolean,
cubrid_fetch_object(res, string ,array) cubrid_fetch_object_construct start1
bool(false)
start2:
bool(false)
start3:
bool(false)
start4:
bool(false)
cubrid_fetch_object(res, string ,array) cubrid_fetch_object_private_construct start1
bool(false)
start5:
bool(false)
start6:
bool(false)
start6:

Fatal error: Class 'this_class_does_not_exist' not found in %s on line %d
