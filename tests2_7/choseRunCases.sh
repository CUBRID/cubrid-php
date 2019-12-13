#!/bin/bash
set -x

function runNormalCases()
{
    echo "#####mv _16_largedata_longtime from the path PHP/php/php#####"
    mv php/_16_largedata_longtime .
    echo "##### start run test cases from _01_schema to _15_newLob#####"
    php run-tests.php php
    echo "##### finished #####"
    mv _16_largedata_longtime php
}


function runLargeDataCases()
{
    echo "#####start to run test cases about large data #####"
    php run-tests.php php/_16_largedata_longtime
    echo "#####finished#####"
}

function runAll()
{
    echo "#####start to run all test cases#####"
    php run-tests.php php
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
    sshuser=`grep -r sshusr connectLarge_ssh.inc | awk '{print $3}' | tr -d ';'`
	sshport=`grep -r sshport connectLarge_ssh.inc | awk '{print $3}' | tr -d ';'`
	sshhost=`grep -r host connectLarge_ssh.inc | awk '{print $3}' | tr -d ';"' | head -1`
	
	ssh -f $sshuser@$sshhost -p $sshport "mkdir $2; cd $2; . ~/.cubrid.sh; cubrid createdb $1 en_US"
	sleep 60
	ssh -f $sshuser@$sshhost -p $sshport "if [ -d ~/largedbFile_bak ]; then cp -rf ~/largedbFile_bak/* ~/largedbFile; fi"
	sleep 600
	ssh -f $sshuser@$sshhost -p $sshport ". ~/.cubrid.sh; cubrid server start $1; cubrid server status"
}

function remote_deleteDB()
{
    sshuser=`grep -r sshusr connectLarge_ssh.inc | awk '{print $3}' | tr -d ';'`
	sshport=`grep -r sshport connectLarge_ssh.inc | awk '{print $3}' | tr -d ';'`
	sshhost=`grep -r host connectLarge_ssh.inc | awk '{print $3}' | tr -d ';"' | head -1`
	
	ssh -f $sshuser@$sshhost -p $sshport ". ~/.cubrid.sh; cubrid server stop $1"
	sleep 60
	ssh -f $sshuser@$sshhost -p $sshport ". ~/.cubrid.sh; cubrid deletedb $1; rm -rf $2"
}

function deleteDB()
{
    cubrid server stop $1
    cubrid deletedb $1
    rm -rf $2
}

############start##########################
#start broker 
if [ $1 == -R ]
then 
sshuser=`grep -r sshusr connectLarge_ssh.inc | awk '{print $3}' | tr -d ';'`
sshport=`grep -r sshport connectLarge_ssh.inc | awk '{print $3}' | tr -d ';'`
sshhost=`grep -r host connectLarge_ssh.inc | awk '{print $3}' | tr -d ';"' | head -1`

ssh -f $sshuser@$sshhost -p $sshport ". ~/.cubrid.sh; cubrid broker start"
ssh -f $sshuser@$sshhost -p $sshport ". ~/.cubrid.sh; cubrid server start demodb;"
else
cubrid broker start
cubrid server start demodb
fi


if [ $1 == -L ]
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
    php largeTable.php

    #start to run test cases about large data
    if [ "$2" == "" ]
    then
    	runLargeDataCases
    else
	php run-tests.php $2
    fi

    #deletedb
    deleteDB largedb largedbFile
    mv connectLarge.inc.ori connectLarge.inc
    mv skipifconnectfailure.inc.ori skipifconnectfailure.inc

    #rm large file
    cd largeFile
    rm -rf large.txt
    cd ..

elif [ $1 == -S ]
then 
    #modify file about: broker port
    modifyPort connect.inc 

    #create database
    createDB phpdb phpdbFile

    if [ "$2" == "" ]
    then
        #start to run test cases about large data
        runNormalCases
    else
        php run-tests.php $2
    fi
    #deletedb
    deleteDB phpdb phpdbFile
    mv connect.inc.ori connect.inc

elif [ $1 == -R ]
then 
    #modify file about: broker port
    #modifyPort connectLarge_ssh.inc 
    #modifyPort connect.inc 
	cp connectLarge_ssh.inc  connectLarge_ssh.inc.ori
	cp connect.inc connect.inc.ori
	
    #create database
    remote_createDB largedb largedbFile
    sleep 20
    remote_createDB phpdb phpdbFile
    sleep 20

	#reuse largedbFile if existed.
	reuse=`ssh -f $sshuser@$sshhost -p $sshport "if [ ! -d ~/largedbFile_bak ]; then echo NOK; else echo OK; fi"`
	if [ $reuse == "NOK" ]
	then
	    #extracting large file
	    cd largeFile
	    tar -zxvf large.tar.gz
	    cd ..

	    #import large data into largedb database
	    php largeTable.php 
            sleep 5
	    ssh -f $sshuser@$sshhost -p $sshport "cp -rf ~/largedbFile ~/largedbFile_bak"
            sleep 600
	fi
    
    #start to run test cases about large data
    runAll

    #deletedb
    remote_deleteDB largedb largedbFile
    remote_deleteDB phpdb phpdbFile
    mv connectLarge_ssh.inc.ori connectLarge_ssh.inc
    mv connect.inc.ori connect.inc

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
    sleep 2
    createDB phpdb phpdbFile
    sleep 2

	if [ ! -d largedbFile_bak ]
    then
       #extracting large file
       cd largeFile
       tar -zxvf large.tar.gz
       cd ..

       #import large data into largedb database
       php largeTable.php
       sleep 5
       #rm large file
       cd largeFile
       rm -rf large.txt
       cd ..
       cp -rf largedbFile largedbFile_bak
       sleep 5
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

