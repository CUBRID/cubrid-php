cubrid createdb -r testdb
cubrid server start testdb
cubrid broker start testdb

php zipinsert.php > test.log

cnt=`cat test.log | grep "Done. 51031 record inserted, 0 errors." | wc -l `

if [ $cnt -eq 1 ]
then
	echo "OK"	
else
	echo "NOK"
fi

cubrid broker stop 
cubrid server stop testdb
cubrid deletedb testdb
rm -rf lob

