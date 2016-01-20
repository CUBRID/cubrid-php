--TEST--
cubrid_except
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc')
?>
--FILE--
<?php

include_once("connect.inc");

$conn = cubrid_connect_with_url($connect_url);
if (!$conn) {
    printf("[001] [%d] %s\n", cubrid_errno($conn), cubrid_error($conn));
    exit(1);
}

//cubrid_execute
cubrid_execute(1, 'DROP TABLE IF EXISTS bit1_tb');
cubrid_execute($conn, 'DROP TABLE IF EXISTS bit2_tb',$conn);

//cubrid_affected_rows
$req = cubrid_execute($conn, "SELECT * FROM code");
cubrid_affected_rows(0);
cubrid_affected_rows($conn);

//cubrid_close_request
cubrid_close_request($req);
cubrid_close_request(0);

//cubrid_current_oid
cubrid_current_oid(0);
$req = cubrid_execute($conn, 'DROP TABLE IF EXISTS bit2_tb',$conn);
cubrid_current_oid($req);

cubrid_num_cols($req);
cubrid_num_cols($req,'error');

cubrid_field_table($req);
cubrid_field_table($req,-1);

cubrid_field_type($req);
cubrid_field_type($req,-1);

cubrid_set_query_timeout($req);
cubrid_set_query_timeout($req,-100);

cubrid_field_len($req);
cubrid_field_len($req,-1);

//cubrid_column_types
cubrid_column_types(0);

//cubrid_column_names
cubrid_column_names(0);

//cubrid_move_cursor
cubrid_move_cursor($req,-1,-1);
cubrid_move_cursor($req,0,1,1,1);

//cubrid_drop
cubrid_drop(0);

//cubrid_get_class_name
cubrid_get_class_name(-1);
cubrid_get_class_name($conn,'error');

//cubrid_col_size
cubrid_col_size(-1);
cubrid_col_size($conn,'test','0000');

//cubrid_col_get
cubrid_col_get(-1);
cubrid_col_get($conn,'test','err');

//cubrid_set_add
cubrid_set_add(-1);
cubrid_set_add($conn,'test','test2','test3');

//cubrid_set_drop
cubrid_set_drop(-1);
cubrid_set_drop($conn,'test','test2','test3');

//cubrid_seq_insert
cubrid_seq_insert(1);
cubrid_seq_insert($conn,'1','2',0,'3');

//cubrid_seq_put
cubrid_seq_put(1);
cubrid_seq_put($conn,'1','2',0,'3');

//cubrid_get_charset
cubrid_get_charset('test');

//cubrid_client_encoding
cubrid_client_encoding('test');

//cubrid_list_dbs
cubrid_list_dbs('test');
cubrid_list_dbs();

cubrid_disconnect($conn);
cubrid_client_encoding();

print "done!";
?>
--CLEAN--
--EXPECTF--
Warning: cubrid_execute() expects parameter 1 to be resource, integer given in %s

Warning: cubrid_execute() expects parameter 3 to be long, resource given in %s

Warning: cubrid_affected_rows() expects parameter 1 to be resource, integer given in %s

Warning: Error: CLIENT, -30002, Invalid API call in %s

Warning: cubrid_close_request() expects parameter 1 to be resource, integer given in %s

Warning: cubrid_current_oid() expects parameter 1 to be resource, integer given in %s

Warning: cubrid_execute() expects parameter 3 to be long, resource given %s

Warning: cubrid_current_oid() expects parameter 1 to be resource, %s

Warning: cubrid_num_cols() expects parameter 1 to be resource, null given %s

Warning: cubrid_num_cols() expects exactly 1 parameter, 2 given in %s

Warning: cubrid_field_table() expects exactly 2 parameters, 1 given in %s

Warning: cubrid_field_table() expects parameter 1 to be resource, null given %s

Warning: cubrid_field_type() expects exactly 2 parameters, 1 given in %s

Warning: cubrid_field_type() expects parameter 1 to be resource, null given in %s

Warning: cubrid_column_types() expects parameter 1 to be resource, integer given %s

Warning: cubrid_column_names() expects parameter 1 to be resource, integer given %s

Warning: cubrid_move_cursor() expects parameter 1 to be resource, null given %s

Warning: cubrid_move_cursor() expects at most 3 parameters, 5 given %s

Warning: cubrid_drop() expects exactly 2 parameters, %s
done!