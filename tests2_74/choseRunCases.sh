#!/bin/bash
set -x
phppath=$1

function runNormalCases()
{
    echo "#####mv _16_largedata_longtime from the path PHP/php/php#####"
    mv php/_16_largedata_longtime .
    echo "##### start run test cases from _01_schema to _15_newLob#####"
    $phppath/php run-tests.php php
    echo "##### finished #####"
    mv _16_largedata_longtime php
}


function runLargeDataCases()
{
    echo "#####start to run test cases about large data #####"
    $phppath/php run-tests.php php/_16_largedata_longtime
    echo "#####finished#####"
}

function runAll()
{
    echo "#####start to run all test cases#####"
    $phppath/php run-tests.php php | tee runall_test.log
    echo "#####finished#####"
}

function modifyPort()
{
    port=`cubrid broker status -b | grep broker1 | awk '{print $4}'`
    cp $1 $1.ori
    sed -i "s/33000/$port/g" $1
}

function createDB()
{
    mkdir $2
    cd $2

    cubrid createdb $1 en_US


    if [ -d ../$2_bak ]
    then
        cp -rf ../$2_bak/* ../$2
    sleep 5
    fi

    cubrid server start $1
    cubrid server status 
    cd ..
}

function remote_createDB()
{
    ssh $sshuser@$sshhost -p $sshport "mkdir $2; cd $2; . ~/.cubrid.sh; cubrid createdb $1 en_US"
	
    if [ $2 == "largedbFile" ]
    then
        ssh $sshuser@$sshhost -p $sshport "if [ -d ~/largedbFile_bak ]; then cp -rf ~/largedbFile_bak/* ~/largedbFile; fi"
    fi
	
    ssh -f $sshuser@$sshhost -p $sshport ". ~/.cubrid.sh; cubrid server start $1; cubrid server status"
    sleep 10
}

function remote_deleteDB()
{
    ssh -f $sshuser@$sshhost -p $sshport ". ~/.cubrid.sh; cubrid server stop $1"
    sleep 10
    ssh -f $sshuser@$sshhost -p $sshport ". ~/.cubrid.sh; cubrid deletedb $1; rm -rf $2"
    sleep 10
}

function deleteDB()
{
    cubrid server stop $1
    cubrid deletedb $1
    rm -rf $2
}

############start##########################
#start broker 
if [ $2 == -R ]
then 
	if [ -e config.properties ]
	then 
		sshuser=`grep -r sshuser config.properties | tr -d ' ' | cut -d'=' -f2`
		sshport=`grep -r sshport config.properties | tr -d ' ' | cut -d'=' -f2`
		sshhost=`grep -r sshhost config.properties | tr -d ' ' | cut -d'=' -f2`
		brokerport=`grep -r brokerport config.properties | tr -d ' ' | cut -d'=' -f2`
	else
		sshuser=cubrid
		sshport=22
		sshhost=`grep -r "^\$host.*;" connect.inc | tr -d ' ;'  | cut -d'=' -f2`
		brokerport=`grep -r "^\$port.*;" connect.inc | tr -d ' ;'  | cut -d'=' -f2`
	fi


	ssh $sshuser@$sshhost -p $sshport ". ~/.cubrid.sh; cubrid broker start"
	ssh $sshuser@$sshhost -p $sshport ". ~/.cubrid.sh; cubrid server start demodb;"
else
	cubrid broker start
	cubrid server start demodb
fi


if [ $2 == -L ]
then 
    #modify file about: broker port
    modifyPort connectLarge.inc
    #modify skipifconnectfailure.inc
    cp skipifconnectfailure.inc skipifconnectfailure.inc.ori
    sed -i "s/connect.inc/connectLarge.inc/g" skipifconnectfailure.inc

    #create database
    createDB largedb largedbFile

    #extracting large file
    cd largeFile
    tar -zxvf large.tar.gz
    cd ..

    #import large data into largedb database
    $phppath/php largeTable.php

    #start to run test cases about large data
    if [ "$3" == "" ]
    then
    	runLargeDataCases
    else
    $phppath/php run-tests.php $3
    fi

    #deletedb
    deleteDB largedb largedbFile
    mv connectLarge.inc.ori connectLarge.inc
    mv skipifconnectfailure.inc.ori skipifconnectfailure.inc

    #rm large file
    cd largeFile
    rm -rf large.txt
    cd ..

elif [ $2 == -S ]
then 
    #modify file about: broker port
    modifyPort connect.inc 

    #create database
    createDB phpdb phpdbFile

    if [ "$3" == "" ]
    then
        #start to run test cases about large data
        runNormalCases
    else
        $phppath/php run-tests.php $3
    fi
    #deletedb
    deleteDB phpdb phpdbFile
    mv connect.inc.ori connect.inc

elif [ $2 == -R ]
then 
	sed -i "s/^\$host.*;/\$host = \"$sshhost\";/" connect.inc
	sed -i "s/^\$port.*;/\$port = $brokerport;/" connect.inc
	sed -i "s/^\$host.*;/\$host = \"$sshhost\";/" connectLarge.inc
	sed -i "s/^\$port.*;/\$port = $brokerport;/" connectLarge.inc
    
    #delete database
    isExistlargedb=`ssh $sshuser@$sshhost -p $sshport "if [ ! -d ~/largedbFile ]; then echo NOK; else echo OK; fi"`
    if [ $isExistlargedb == "OK" ]
    then
        remote_deleteDB largedb largedbFile
    fi

    isExistphpdb=`ssh $sshuser@$sshhost -p $sshport "if [ ! -d ~/phpdbFile ]; then echo NOK; else echo OK; fi"`
    if [ $isExistphpdb == "OK" ]
    then
        remote_deleteDB phpdb phpdbFile
    fi

    #create database
    remote_createDB largedb largedbFile
    remote_createDB phpdb phpdbFile

	#reuse largedbFile if existed.
	reuse=`ssh $sshuser@$sshhost -p $sshport "if [ ! -d ~/largedbFile_bak ]; then echo NOK; else echo OK; fi"`

	if [ $reuse == "NOK" ]
	then
	    #extracting large file
	    cd largeFile
	    tar -zxvf large.tar.gz
	    cd ..

	    #import large data into largedb database
	    $phppath/php largeTable.php 
        
	    ssh $sshuser@$sshhost -p $sshport "cp -rf ~/largedbFile ~/largedbFile_bak"
        
	fi
    
    #start to run test cases about large data
    runAll

    #deletedb
    remote_deleteDB largedb largedbFile
    remote_deleteDB phpdb phpdbFile

    #rm large file
    cd largeFile
    rm -rf large.txt
    cd ..

else
    #default is to run all of test cases 
    #modify file about: broker port
    modifyPort connectLarge.inc
    modifyPort connect.inc 

    #create database
    createDB largedb largedbFile
    
    createDB phpdb phpdbFile
    

	if [ ! -d largedbFile_bak ]
    then
       #extracting large file
       cd largeFile
       tar -zxvf large.tar.gz
       cd ..

       #import large data into largedb database
       $phppath/php largeTable.php
       
       #rm large file
       cd largeFile
       rm -rf large.txt
       cd ..
       cp -rf largedbFile largedbFile_bak
       
    fi
    
    #start to run test cases about large data
    runAll

    #deletedb
    deleteDB largedb largedbFile
    deleteDB phpdb phpdbFile
    mv connectLarge.inc.ori connectLarge.inc
    mv connect.inc.ori connect.inc

    #rm large file
    cd largeFile
    rm -rf large.txt
    cd ..
fi

