--TEST--
cubrid_field_flag
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
//CUBRID_SCH_PRIMARY_KEY
include_once("connect.inc");
$conn = cubrid_connect_with_url($connect_url, $user, $passwd);
cubrid_execute($conn,"drop table if EXISTS album;");
cubrid_execute($conn,"CREATE TABLE album(id_1 char(10) , id_2 char(10) , id_3 char(10) , id_4 char(10) , id_5 char(10) ,
 id_6 char(10) , id_7 char(10) , id_8 char(10) , id_9 char(10) , id_10 char(10) ,
 id_11 char(10) , id_12 char(10) , id_13 char(10) , id_14 char(10) , id_15 char(10) ,
 id_16 char(10) , id_17 char(10) , id_18 char(10) , id_19 char(10) , id_20 char(10) ,
 id_21 char(10) , id_22 char(10) , id_23 char(10) , id_24 char(10) , id_25 char(10) ,
 id_26 char(10) , id_27 char(10) , id_28 char(10) , id_29 char(10) , id_30 char(10) ,
 id_31 char(10) , id_32 char(10) , id_33 char(10) , id_34 char(10) , id_35 char(10) ,
  CONSTRAINT \"pk_album_id\" PRIMARY KEY (id_1, id_2, id_3, id_4, id_5, id_6, id_7, id_8, id_9, id_10,id_11, id_12, id_13, id_14, id_15, id_16, id_17, id_18, id_19, id_20,id_21, id_22, id_23, id_24, id_25, id_26, id_27, id_28, id_29, id_30,id_31, id_32, id_33, id_34, id_35))");

print("#####positive example#####\n");
$schema1 = cubrid_schema($conn,CUBRID_SCH_PRIMARY_KEY,"album");
var_dump($schema1);


cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
array(35) {
  [0]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(4) "id_1"
    ["KEY_SEQ"]=>
    string(1) "1"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [1]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_10"
    ["KEY_SEQ"]=>
    string(2) "10"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [2]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_11"
    ["KEY_SEQ"]=>
    string(2) "11"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [3]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_12"
    ["KEY_SEQ"]=>
    string(2) "12"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [4]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_13"
    ["KEY_SEQ"]=>
    string(2) "13"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [5]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_14"
    ["KEY_SEQ"]=>
    string(2) "14"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [6]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_15"
    ["KEY_SEQ"]=>
    string(2) "15"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [7]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_16"
    ["KEY_SEQ"]=>
    string(2) "16"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [8]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_17"
    ["KEY_SEQ"]=>
    string(2) "17"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [9]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_18"
    ["KEY_SEQ"]=>
    string(2) "18"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [10]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_19"
    ["KEY_SEQ"]=>
    string(2) "19"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [11]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(4) "id_2"
    ["KEY_SEQ"]=>
    string(1) "2"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [12]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_20"
    ["KEY_SEQ"]=>
    string(2) "20"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [13]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_21"
    ["KEY_SEQ"]=>
    string(2) "21"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [14]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_22"
    ["KEY_SEQ"]=>
    string(2) "22"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [15]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_23"
    ["KEY_SEQ"]=>
    string(2) "23"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [16]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_24"
    ["KEY_SEQ"]=>
    string(2) "24"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [17]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_25"
    ["KEY_SEQ"]=>
    string(2) "25"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [18]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_26"
    ["KEY_SEQ"]=>
    string(2) "26"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [19]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_27"
    ["KEY_SEQ"]=>
    string(2) "27"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [20]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_28"
    ["KEY_SEQ"]=>
    string(2) "28"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [21]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_29"
    ["KEY_SEQ"]=>
    string(2) "29"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [22]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(4) "id_3"
    ["KEY_SEQ"]=>
    string(1) "3"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [23]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_30"
    ["KEY_SEQ"]=>
    string(2) "30"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [24]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_31"
    ["KEY_SEQ"]=>
    string(2) "31"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [25]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_32"
    ["KEY_SEQ"]=>
    string(2) "32"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [26]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_33"
    ["KEY_SEQ"]=>
    string(2) "33"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [27]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_34"
    ["KEY_SEQ"]=>
    string(2) "34"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [28]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(5) "id_35"
    ["KEY_SEQ"]=>
    string(2) "35"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [29]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(4) "id_4"
    ["KEY_SEQ"]=>
    string(1) "4"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [30]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(4) "id_5"
    ["KEY_SEQ"]=>
    string(1) "5"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [31]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(4) "id_6"
    ["KEY_SEQ"]=>
    string(1) "6"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [32]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(4) "id_7"
    ["KEY_SEQ"]=>
    string(1) "7"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [33]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(4) "id_8"
    ["KEY_SEQ"]=>
    string(1) "8"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
  [34]=>
  array(4) {
    ["CLASS_NAME"]=>
    string(5) "album"
    ["ATTR_NAME"]=>
    string(4) "id_9"
    ["KEY_SEQ"]=>
    string(1) "9"
    ["KEY_NAME"]=>
    string(11) "pk_album_id"
  }
}
Finished!
