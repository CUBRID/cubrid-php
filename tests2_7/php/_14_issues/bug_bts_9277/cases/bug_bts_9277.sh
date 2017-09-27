#!/bin/bash
. $init_path/init.sh
init test
set -x

db=db_9277
rm -rf $db
mkdir $db
cd $db
cubrid createdb --db-volume-size=20m $db
cubrid server start $db
cd ..

cubrid broker start
port1=`cubrid broker status -b | grep broker1 | awk '{print $4}'`
port2=`cubrid broker status -b | grep query_editor| awk '{print $4}'`

#get ip address
a=`cat /etc/sysconfig/network-scripts/ifcfg-eth0|grep IPADDR|awk '{print $1}'`
test-db-server="${a#*=}"

sed -i "s/33199/$port1/g" connect.inc
sed -i "s/30199/$port2/g" connect.inc
sed -i "s/10.34.64.61/$test-db-server/g" connect.inc

csql -udba $db -c"drop table if EXISTS foo;create table foo(a int); insert into foo values(1);"

output_file=${case_name}.output
php test.php >$output_file

compare_result_between_files "${output_file}" "${case_name}.answer"

cubrid server stop $db
cubrid deletedb $db
rm -rf $db

finish
