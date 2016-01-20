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
    sed -i "s/33199/$port/g" $1
}

function createDB()
{
    mkdir $2
    cd $2
    cubrid createdb $1
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

    #create database
    createDB largedb largedbFile

    #extracting large file
    cd largeFile
    tar -zxvf large.tar.gz
    cd ..

    #import large data into largedb database
    php largeTable.php

    #start to run test cases about large data
    runLargeDataCases

    #deletedb
    deleteDB largedb largedbFile

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

    #start to run test cases about large data
    runNormalCases

    #deletedb
    deleteDB phpdb phpdbFile

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

    #rm large file
    cd largeFile
    rm -rf large.txt
    cd ..
fi

