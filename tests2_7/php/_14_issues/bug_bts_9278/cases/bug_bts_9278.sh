#!/bin/bash
. $init_path/init.sh
init test
set -x

db=db_9299
rm -rf $db
mkdir $db
cd $db
cubrid createdb --db-volume-size=20m $db
cubrid server start $db
cd ..

cubrid broker start
port=`cubrid broker status -b | grep broker1 | awk '{print $4}'`

sed -i "s/33199/$port/g" connect.inc

csql -udba $db -c"drop table if EXISTS cubridsus9278;create table cubridsus9278(a int auto_increment, b char(200000));"


php test.php
csql -udba $db -c"select count(*) from cubridsus9278 where b='aaaa';" >result.log
compare_result_between_files result.log answer.txt
cubrid server stop $db
cubrid deletedb $db
rm -rf $db

finish
