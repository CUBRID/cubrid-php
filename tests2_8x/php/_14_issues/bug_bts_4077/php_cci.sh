#! /bin/bash

set x

current_dir=$(cd "$(dirname "${0}")"; pwd)

echo $current_dir
cubrid service stop
pkill cub
cubrid deletedb phptests

cd $current_dir
if [ -e phpdb ]; then
	rm -rf  phpdb
fi
mkdir phpdb
cubrid createdb phptests -F "$current_dir/phpdb"
cubrid server start phptests
cd $current_dir

# multi_connection
if [ $# == 1 ] 
then
	echo "--------Group Test Cas---------------------begin--------------------"

	echo "CCI_DEFAULT_AUTOCOMMIT = ON" >>$CUBRID/conf/cubrid_broker.conf
	cubrid broker restart

	i=0
	while [ $i -lt 5 ]
	do
	phpunit --group "cci_multi" cubrid.php&
	phpunit --filter "testCubridCci6_t9" cubrid.php
	phpunit --filter "testCubridCci6_t10" cubrid.php
	i=$[$i+1]
	done

	echo "CCI_DEFAULT_AUTOCOMMIT = ON" >>$CUBRID/conf/cubrid_broker.conf

	echo "-------Group Test Cas---------------------end--------------------"

fi
if [ $# == 0 ]
then
	echo "--------Single Test------------------begin----------------------------"

	echo "CCI_DEFAULT_AUTOCOMMIT = ON" >>$CUBRID/conf/cubrid_broker.conf
	cubrid broker restart
	phpunit --filter "testCubridCci1" cubrid.php
	sed -i '/CCI_DEFAULT_AUTOCOMMIT = ON/d' $CUBRID/conf/cubrid_broker.conf

	echo "CCI_DEFAULT_AUTOCOMMIT = OFF" >>$CUBRID/conf/cubrid_broker.conf
	cubrid broker restart
	phpunit --filter "testCubridCci2" cubrid.php
	sed -i '/CCI_DEFAULT_AUTOCOMMIT = OFF/d' $CUBRID/conf/cubrid_broker.conf

	echo "CCI_DEFAULT_AUTOCOMMIT = ON" >>$CUBRID/conf/cubrid_broker.conf
	cubrid broker restart
	phpunit --filter "testCubridCci3" cubrid.php
	sed -i '/CCI_DEFAULT_AUTOCOMMIT = ON/d' $CUBRID/conf/cubrid_broker.conf

	echo "CCI_DEFAULT_AUTOCOMMIT = OFF" >>$CUBRID/conf/cubrid_broker.conf
	cubrid broker restart
	phpunit --filter "testCubridCci4" cubrid.php
	sed -i '/CCI_DEFAULT_AUTOCOMMIT = OFF/d' $CUBRID/conf/cubrid_broker.conf

	echo "CCI_DEFAULT_AUTOCOMMIT = ON" >>$CUBRID/conf/cubrid_broker.conf
	cubrid broker restart
	phpunit --filter "testCubridCci5" cubrid.php
	sed -i '/CCI_DEFAULT_AUTOCOMMIT = ON/d' $CUBRID/conf/cubrid_broker.conf

	echo "--------Single Test------------------end----------------------------"
fi




