#!/bin/bash

cubrid createdb -r testdb
cubrid server start testdb
cubrid broker start testdb

#######basic function test###################
csql -udba testdb -i insert.sql
php test-fetch.php > test-fetch.log

cnt=`cat test-fetch.log|grep "b:"|wc -l`
if [ $cnt -eq 65536 ]
then 
   echo "OK"
else
   echo "NOK" 
fi


#########error handling test################################
csql -udba testdb -c "drop table if exists foo"
csql -udba testdb -c "create table foo (a int, b int, primary key (a));insert into foo values (1,1);"
php prepare_error.php > prepare_error.log 2>&1
cnt2=`cat prepare_error.log |grep "PHP Warning:  Error: DBMS, -493, Syntax: Unknown class \"foo1\"."|wc -l`
if [ $cnt2 -eq 1 ]
then
   echo "OK"
else
   echo "NOK" 
fi

cnt3=`cat prepare_error.log |grep "PHP Warning:  cubrid_fetch() expects parameter 1 to be resource,"|wc -l`
if [ $cnt3 -eq 1 ]
then
   echo "OK"
else
   echo "NOK" 
fi


php run_error.php > run_error.log 2>&1
cnt4=`cat run_error.log |grep "PHP Warning:  Error: DBMS, -670, Operation would have caused one or more unique constraint violations."|wc -l`
if [ $cnt4 -eq 1 ]
then
   echo "OK"
else
   echo "NOK" 
fi

cnt5=`cat run_error.log |grep "PHP Warning:  cubrid_fetch() expects parameter 1 to be resource"|wc -l`
if [ $cnt5 -eq 1 ]
then
   echo "OK"
else
   echo "NOK" 
fi

cubrid broker stop 
cubrid server stop testdb
cubrid deletedb testdb
rm -rf lob 

