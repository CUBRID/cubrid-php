@echo off
setlocal enabledelayedexpansion

set SHELL_PATH=%~dp0
set sshuser=cubrid
set sshport=22
set sshhost=test-db-server
set brokerport=33000
set phppath=%2
setx TEST_PHP_EXECUTABLE %phppath%\php.exe
setx TEST_PHP_CGI_EXECUTABLE %phppath%\php-cgi.exe
set php=%TEST_PHP_EXECUTABLE%

:: Start broker
if "%php%" == "\php.exe" (
  set php=php
) 

echo "php file : %php%"
if "%1" == "-R" (
    echo "sshuser = !sshuser!"
    echo "sshhost = %sshhost%"
    echo "sshport = %sshport%"
    ssh %sshuser%@%sshhost% -p %sshport% "source ~/.cubrid.sh && cubrid broker start"
    echo "2"
    ssh %sshuser%@%sshhost% -p %sshport% "source ~/.cubrid.sh && cubrid server start demodb"
) else (
    cubrid broker start
    cubrid server start demodb
)

if "%1" == "-L" (
    :: Modify file about: broker port
    call :modifyPort connectLarge.inc
    :: Modify skipifconnectfailure.inc
    copy skipifconnectfailure.inc skipifconnectfailure.inc.ori
    call :replace_in_file skipifconnectfailure.inc "connect.inc" "connectLarge.inc"

    :: Create database
    call :createDB largedb largedbFile

    :: Extracting large file
    cd largeFile
    tar -zxvf large.tar.gz
    cd ..

    :: Import large data into largedb database
    %php% largeTable.php

    :: Start to run test cases about large data
    if "%2" == "" (
        call :runLargeDataCases
    ) else (
        %php% run-tests.php %2
    )

    :: DeleteDB
    call :deleteDB largedb largedbFile
    move connectLarge.inc.ori connectLarge.inc
    move skipifconnectfailure.inc.ori skipifconnectfailure.inc

    :: Remove large file
    cd largeFile
    del large.txt
    cd ..
) else if "%1" == "-S" (
    :: Modify file about: broker port
    call :modifyPort connect.inc

    :: Create database
    call :createDB phpdb phpdbFile

    if "%2" == "" (
        :: Start to run test cases about normal data
        call :runNormalCases
    ) else (
        %php% run-tests.php %2
    )

    :: DeleteDB
    call :deleteDB phpdb phpdbFile
    move connect.inc.ori connect.inc
) else if "%1" == "-R" (
    :: DeleteDB
    call :remote_deleteDB largedb largedbFile
    call :remote_deleteDB phpdb phpdbFile     

    :: Create database
    call :remote_createDB largedb largedbFile
    call :remote_createDB phpdb phpdbFile

    :: Start to run test cases about large data
    call :runAll

    :: DeleteDB
    call :remote_deleteDB largedb largedbFile
    call :remote_deleteDB phpdb phpdbFile

) else (
    :: Default is to run all test cases
    :: Modify file about: broker port
    call :modifyPort connectLarge.inc
    call :modifyPort connect.inc

    :: Create database
    call :createDB largedb largedbFile
    timeout /t 2
    call :createDB phpdb phpdbFile
    timeout /t 2

    if not exist largedbFile_bak (
        :: Extracting large file
        cd largeFile
        tar -zxvf large.tar.gz
        cd ..

        :: Import large data into largedb database
        %php% largeTable.php
        timeout /t 5

        :: Remove large file
        cd largeFile
        del large.txt
        cd ..
        xcopy /E /I largedbFile largedbFile_bak
        timeout /t 5
    )

    :: Start to run test cases about large data
    call :runAll

    :: DeleteDB
    call :deleteDB largedb largedbFile
    call :deleteDB phpdb phpdbFile
    move connectLarge.inc.ori connectLarge.inc
    move connect.inc.ori connect.inc

    :: Remove large file
    cd largeFile
    del large.txt
    cd ..
)

endlocal
exit /b

:modifyPort
echo "modifyPort %1"
setlocal
set file=%1
set port=33000
for /f "tokens=4" %%a in ('cubrid broker status -b ^| findstr broker1') do set port=%%a
copy %file% %file%.ori
powershell -Command "(Get-Content %file% | ForEach-Object {$_ -replace '33000', '%port%'}) | Set-Content %file%"
endlocal
exit /b

:replace_in_file
echo "replace_in_file %1 %2 %3"
setlocal
set file=%1
set old_string=%2
set new_string=%3
powershell -Command "(Get-Content %file% | ForEach-Object {$_ -replace '%old_string%', '%new_string%'}) | Set-Content %file%"
endlocal
exit /b

:createDB
echo "createDB %1 %2 %db_dir%"
setlocal
set db_name=%1
set db_dir=%2
mkdir %db_dir%
cd %db_dir%
cubrid createdb %db_name% en_US
if exist ..\%db_dir%_bak (
    xcopy /E /I ..\%db_dir%_bak\* %db_dir%
    timeout /t 5
)
cubrid server start %db_name%
cubrid server status
cd ..
endlocal
exit /b

:remote_createDB
setlocal
echo "remote_createDB %1 %2"

ssh %sshuser%@%sshhost% -p %sshport% "mkdir -p %2; cd %2; source ~/.cubrid.sh; cubrid createdb %1 en_US"
call :error_check %ERRORLEVEL% "createdb %1"
for /f "delims=" %%i in ('ssh %sshuser%@%sshhost% -p %sshport% "if test -d ~/largedbFile_bak; then echo 'File exists'; else echo 'File does not exist'; fi"') do set file_check=%%i
if "%1%" == "largedb" (
  if "%file_check%" == "File exists" (
    echo The file exists. so largedbFile is copy"
    ssh %sshuser%@%sshhost% -p %sshport% "mkdir -p largedbFile; cp -rf ~/largedbFile_bak/* ~/largedbFile"
  ) else (
    :: Extracting large file
    cd largeFile
    tar -zxvf large.tar.gz
    cd ..

    :: Import large data into largedb database
    %php% largeTable.php
    timeout /t 5

    :: Remove large file
    cd largeFile
    del large.txt
    cd ..
    ssh %sshuser%@%sshhost% -p %sshport% "cp -rf ~/largedbFile ~/largedbFile_bak"
    timeout /t 5
  )
)

ssh %sshuser%@%sshhost% -p %sshport% "source ~/.cubrid.sh; nohup cubrid server start %1 > /dev/null 2>&1 &"
call :error_check %ERRORLEVEL% "server start %1"
timeout /t 10
endlocal
exit /b

:remote_deleteDB
echo "remote_deleteDB %1 %2"
setlocal
ssh %sshuser%@%sshhost% -p %sshport% "source ~/.cubrid.sh; cubrid server stop %1"
call :error_check %ERRORLEVEL% "stop"
ssh %sshuser%@%sshhost% -p %sshport% "source ~/.cubrid.sh; cubrid deletedb %1 && rm -rf ~/%2"
call :error_check %ERRORLEVEL% "delete %1"
endlocal
exit /b

:deleteDB
setlocal
cubrid server stop %1
cubrid deletedb %1
rmdir /S /Q %2
endlocal
exit /b

:error_check
setlocal
if %1 neq 0 (
  echo "error %2"
) else (
  echo "%2"
)
setlocal
exit /b

:runNormalCases
echo #####mv _16_largedata_longtime from the path PHP/php/php#####
move php\_16_largedata_longtime .
echo ##### start run test cases from _01_schema to _15_newLob#####
%php% run-tests.php php
echo ##### finished #####
move _16_largedata_longtime php
exit /b

:runLargeDataCases
echo #####start to run test cases about large data#####
%php% run-tests.php php\_16_largedata_longtime
echo #####finished#####
exit /b

:runAll
echo #####start to run all test cases : %php%#####
%php% run-tests.php php
echo #####finished#####
exit /b

