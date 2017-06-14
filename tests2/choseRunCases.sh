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
    cubrid server start $1
    cubrid server status 
    cd ..
}

function deleteDB()
{
    cubrid server stop $1
    cubrid deletedb $1
    rm -rf $2
}

############start##########################
#start broker 
cubrid broker start
cubrid server start demodb

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

    #extracting large file
    cd largeFile
    tar -zxvf large.tar.gz
    cd ..

    #import large data into largedb database
    php largeTable.php 
    
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

