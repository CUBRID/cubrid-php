--TEST--
cubrid_execute: sql statements are about calculate
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
include_once('connect.inc');
$conn = cubrid_connect($host, $port, $db, $user, $passwd);

printf("#####calculate result#####\n");
//Date calculate
$result =cubrid_execute($conn," select date'2002-01-01' - datetime'2001-02-02 12:00:00 am';");
$row = cubrid_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =cubrid_execute($conn,"SELECT date'2002-01-01' + '10';" );
$row = cubrid_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =cubrid_execute($conn, "SELECT 4 + '5.2'");
$row = cubrid_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =cubrid_execute($conn, "SELECT DATE'2002-01-01'+1;");
$row = cubrid_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =cubrid_execute($conn,"SELECT '1'+'1';" );
$row = cubrid_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =cubrid_execute($conn, "SELECT '3'*'2';");
$row = cubrid_fetch_array($result, CUBRID_ASSOC);
print_r($row);

//LENGTH calculate
$result =cubrid_execute($conn,"select BIT_LENGTH('CUBRID');" );
$row = cubrid_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =cubrid_execute($conn,"select BIT_LENGTH(B'010101010');" );
$row = cubrid_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =cubrid_execute($conn,"SELECT LENGTH('');" );
$row = cubrid_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =cubrid_execute($conn,"SELECT CHR(68) || CHR(68-2);" );
$row = cubrid_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =cubrid_execute($conn,"SELECT CONCAT('CUBRID', '2008' , 'R3.0',NULL)" );
$row = cubrid_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =cubrid_execute($conn,"SELECT INSTR ('12345abcdeabcde','b', -1);" );
$row = cubrid_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =cubrid_execute($conn,"SELECT ((CAST ({3,3,3,2,2,1} AS SET))+(CAST ({4,3,3,2} AS MULTISET)));" );
$row = cubrid_fetch_array($result, CUBRID_ASSOC);
print_r($row);

$result =cubrid_execute($conn,"SELECT (CAST(TIMESTAMP'2008-12-25 10:30:20' AS TIME));" );
$row = cubrid_fetch_array($result, CUBRID_ASSOC);
print_r($row);

if(!$result =cubrid_execute($conn,"SELECT (CAST(1234.567890 AS CHAR(5)));" )){
   printf("[%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
}else{
   $row = cubrid_fetch_array($result, CUBRID_ASSOC);
   print_r($row);
}

cubrid_close($conn);
print 'Finished!';
?>
--CLEAN--
--EXPECTF--
#####calculate result#####
Array
(
    [date '2002-01-01'-datetime '2001-02-02 12:00:00 am'] => 28771200000
)
Array
(
    [date '2002-01-01'+'10'] => 2002-01-11
)
Array
(
    [4+'5.2'] => 9.1999999999999993
)
Array
(
    [date '2002-01-01'+1] => 2002-01-02
)
Array
(
    ['1'+'1'] => 11
)
Array
(
    ['3'*'2'] => 6.0000000000000000
)
Array
(
    [bit_length('CUBRID')] => 48
)
Array
(
    [bit_length(B'010101010')] => 9
)
Array
(
    [char_length('')] => 0
)
Array
(
    [chr(68)|| chr(68-2)] => DB
)
Array
(
    [concat('CUBRID', '2008', 'R3.0', null)] => 
)
Array
(
    [instr('12345abcdeabcde', 'b', -1)] => 12
)
Array
(
    [(( cast({3, 3, 3, 2, 2, 1} as set))+( cast({4, 3, 3, 2} as multiset)))] => Array
        (
            [0] => 1
            [1] => 2
            [2] => 2
            [3] => 3
            [4] => 3
            [5] => 3
            [6] => 4
        )

)
Array
(
    [( cast(timestamp '2008-12-25 10:30:20' as time))] => 10:30:20
)

Warning: Error: DBMS, -181, Cannot coerce value of domain "numeric" to domain "character". in %s on line %d
[-181] Cannot coerce value of domain "numeric" to domain "character".
Finished!
