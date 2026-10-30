@echo off
setlocal enabledelayedexpansion

set SHELL_PATH=%~dp0
set sshuser=cubrid
set sshport=22
set sshhost=127.0.0.1
set brokerport=33000
rem connect.inc / connectLarge.inc read the DB host from CUBRID_TEST_HOST
if not defined CUBRID_TEST_HOST set CUBRID_TEST_HOST=%sshhost%
set SSH_CMD=ssh -n -p %sshport% %sshuser%@%sshhost%
rem usage: choseRunCases.bat [-S^|-L^|-R^|-SR] [phppath] [target]
rem   phppath : directory of php.exe (default: php in PATH), e.g. D:\PHP\php-8.5.8-nts-Win32-vs17-x64
rem   target  : run only this test file/directory
set mode=%~1
set phppath=%~2
set target=%~3
rem No option (all cases): choseRunCases.bat [phppath] [target]
if defined mode if not "!mode:~0,1!" == "-" (
  set "target=%~2"
  set "phppath=%~1"
  set "mode="
)
if defined phppath if "!phppath:~-1!" == "\" set "phppath=!phppath:~0,-1!"
set php=php
if not "%phppath%" == "" set php=%phppath%\php.exe
if not "%phppath%" == "" if not exist "%php%" (
  echo [ERROR] php not found: %php%
  exit /b 1
)
if "%phppath%" == "" (
  where php >nul 2>nul
  if errorlevel 1 (
    echo [ERROR] php is not in PATH. Give the php directory:
    echo         choseRunCases.bat %mode% D:\PHP\php-8.5.8-Win32-vs17-x64 [target]
    exit /b 1
  )
)
rem run-tests.php reads these from the current process environment (setx does not set them here)
set TEST_PHP_EXECUTABLE=%php%
if not "%phppath%" == "" set TEST_PHP_CGI_EXECUTABLE=%phppath%\php-cgi.exe

rem ---- cubrid extension -------------------------------------------------------
rem Without it every test is skipped by skipif.inc ("CUBRID extension not available").
rem Default: ..\build_win\<ver>-<nts|ts>\modules\php_cubrid.dll built by ..\build_win.ps1,
rem <ver> = 84, 85 and nts/ts taken from the php binary. Override with CUBRID_EXT.
rem If it does not exist, the cubrid extension loaded by php.ini is used.
rem for /f mangles the PHP code, so read the output through a file. "851" = PHP 8.5 TS
set "PHP_INFO="
set "INFO_FILE=%TEMP%\cubrid_choseRunCases_%RANDOM%.txt"
"%php%" -n -r "echo PHP_MAJOR_VERSION * 100 + PHP_MINOR_VERSION * 10 + PHP_ZTS;" > "%INFO_FILE%" 2>nul
set /p PHP_INFO=<"%INFO_FILE%"
del "%INFO_FILE%" 2>nul
if not defined PHP_INFO (
  echo [ERROR] Cannot run %php%
  exit /b 1
)
set "PHP_VER=%PHP_INFO:~0,2%"
set "PHP_FLAVOR=nts"
if "%PHP_INFO:~2,1%" == "1" set "PHP_FLAVOR=ts"
if not defined CUBRID_EXT set "CUBRID_EXT=%SHELL_PATH%..\build_win\%PHP_VER%-%PHP_FLAVOR%\modules\php_cubrid.dll"
set "php_opts="
if exist "%CUBRID_EXT%" goto :ext_found
"%php%" -r "exit(extension_loaded('cubrid') ? 0 : 1);" 2>nul
if errorlevel 1 (
  set "TS_OPT="
  if "%PHP_FLAVOR%" == "ts" set "TS_OPT= -ThreadSafe"
  echo [ERROR] %CUBRID_EXT% not found and %php% has no cubrid extension.
  echo         Run '..\build_win.ps1 %PHP_VER%!TS_OPT!' first.
  exit /b 1
)
echo "cubrid extension : loaded by php.ini"
goto :ext_done
:ext_found
for %%f in ("%CUBRID_EXT%") do (
  set "CUBRID_EXT=%%~ff"
  set "EXT_DIR=%%~dpf"
)
rem php_cubrid.dll needs cascci.dll, which build_win.ps1 puts next to it.
set "PATH=%EXT_DIR%;%PATH%"
set php_opts=-n -d extension="%CUBRID_EXT%"
echo "cubrid extension : %CUBRID_EXT%"
:ext_done
rem php with the extension, and run-tests.php that passes the extension to every test
set PHP_RUN="%php%" %php_opts%
set RUN_TESTS="%php%" run-tests.php -q %php_opts% -p "%php%"
set NO_COLOR=1

echo "php file : %php% (%PHP_VER%, %PHP_FLAVOR%)"

set IS_REMOTE=0
for %%A in (-R -SR) do if "%mode%"=="%%A" set IS_REMOTE=1

if "%IS_REMOTE%"=="1" (
    echo "sshuser = !sshuser!"
    echo "sshhost = %sshhost%"
    echo "sshport = %sshport%"
    call :remote_start "broker start"
    call :remote_start "server start demodb"
) else (
    cubrid broker start
    cubrid server start demodb
)

if "%mode%" == "-L" (
    rem Modify file about: broker port
    call :modifyPort connectLarge.inc
    rem Modify skipifconnectfailure.inc
    copy skipifconnectfailure.inc skipifconnectfailure.inc.ori
    call :replace_in_file skipifconnectfailure.inc "connect.inc" "connectLarge.inc"

    rem Create database
    call :createDB largedb largedbFile

    rem Extracting large file
    cd largeFile
    tar -zxvf large.tar.gz
    cd ..

    rem Import large data into largedb database
    %PHP_RUN% largeTable.php

    rem Start to run test cases about large data
    if "%target%" == "" (
        call :runLargeDataCases
    ) else (
        %RUN_TESTS% %target%
    )

    rem DeleteDB
    call :deleteDB largedb largedbFile
    move connectLarge.inc.ori connectLarge.inc
    move skipifconnectfailure.inc.ori skipifconnectfailure.inc

    rem Remove large file
    cd largeFile
    del large.txt
    cd ..
) else if "%mode%" == "-S" (
    rem Modify file about: broker port
    call :modifyPort connect.inc

    rem Create database
    call :createDB phpdb phpdbFile

    if "%target%" == "" (
        rem Start to run test cases about normal data
        call :runNormalCases
    ) else (
        %RUN_TESTS% %target%
    )

    rem DeleteDB
    call :deleteDB phpdb phpdbFile
    move connect.inc.ori connect.inc
) else if "%mode%" == "-R" (
    echo "-R option start"
    rem DeleteDB
    call :remote_deleteDB largedb largedbFile
    call :remote_deleteDB phpdb phpdbFile

    rem Create database
    call :remote_createDB largedb largedbFile
    call :remote_createDB phpdb phpdbFile

    rem Start to run test cases about large data
    call :runAll

    rem DeleteDB
    call :remote_deleteDB largedb largedbFile
    call :remote_deleteDB phpdb phpdbFile

) else if "%mode%" == "-SR" (
    echo "-SR option start"
    rem DeleteDB
    call :remote_deleteDB phpdb phpdbFile

    rem Create database
    call :remote_createDB phpdb phpdbFile

    if "%target%" == "" (
        rem Start to run test cases about normal data
        call :runNormalCases
    ) else (
        %RUN_TESTS% %target%
    )

    rem DeleteDB
    call :remote_deleteDB phpdb phpdbFile
) else (
    rem Default is to run all test cases
    rem Modify file about: broker port
    call :modifyPort connectLarge.inc
    call :modifyPort connect.inc

    rem Create database
    call :createDB largedb largedbFile
    timeout /t 2
    call :createDB phpdb phpdbFile
    timeout /t 2

    if not exist largedbFile_bak (
        rem Extracting large file
        cd largeFile
        tar -zxvf large.tar.gz
        cd ..

        rem Import large data into largedb database
        %PHP_RUN% largeTable.php
        timeout /t 5

        rem Remove large file
        cd largeFile
        del large.txt
        cd ..
        xcopy /E /I largedbFile largedbFile_bak
        timeout /t 5
    )

    rem Start to run test cases about large data
    call :runAll

    rem DeleteDB
    call :deleteDB largedb largedbFile
    call :deleteDB phpdb phpdbFile
    move connectLarge.inc.ori connectLarge.inc
    move connect.inc.ori connect.inc

    rem Remove large file
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

%SSH_CMD% "mkdir -p %2; cd %2; source ~/.cubrid.sh; cubrid createdb %1 en_US"
call :error_check %ERRORLEVEL% "createdb %1"
for /f "delims=" %%i in ('%SSH_CMD% "if test -d ~/largedbFile_bak; then echo 'File exists'; else echo 'File does not exist'; fi"') do set file_check=%%i
if "%1" == "largedb" (
  if "%file_check%" == "File exists" (
    echo "The file exists. so largedbFile is copied"
    %SSH_CMD% "mkdir -p largedbFile; cp -rf ~/largedbFile_bak/* ~/largedbFile"
  )
)

rem The server must be running before largeTable.php connects to it.
call :remote_start "server start %1"
call :error_check %ERRORLEVEL% "server start %1"

if "%1" == "largedb" (
  if not "%file_check%" == "File exists" (
    rem Extracting large file
    cd largeFile
    tar -zxvf large.tar.gz
    cd ..

    rem Import large data into largedb database
    %PHP_RUN% largeTable.php
    set import_rc=!ERRORLEVEL!
    call :error_check !import_rc! "import large data"
    timeout /t 5

    rem Remove large file
    cd largeFile
    del large.txt
    cd ..
    rem Back up only a complete import, and while the server is stopped so the copy is consistent.
    if "!import_rc!" == "0" (
      call :remote_start "server stop %1"
      %SSH_CMD% "rm -rf ~/largedbFile_bak; cp -rf ~/largedbFile ~/largedbFile_bak"
      call :remote_start "server start %1"
      call :error_check !ERRORLEVEL! "server start %1"
    )
  )
)
timeout /t 10
endlocal
exit /b

rem Run "cubrid <args>" on the remote host and return when it finishes.
rem Daemons started by it (cub_broker, cub_server, cub_pl) inherit the ssh session's
rem stdin/stdout/stderr and the extra fd 6 that sshd passes, so ssh never returns
rem unless all of them are detached. Output goes to a temp log that is printed afterwards.
:remote_start
setlocal
%SSH_CMD% "source ~/.cubrid.sh; log=$(mktemp); cubrid %~1 < /dev/null > $log 2>&1 6>&-; rc=$?; cat $log; rm -f $log; exit $rc"
set rc=%ERRORLEVEL%
endlocal & exit /b %rc%

:remote_deleteDB
echo "remote_deleteDB %1 %2"
setlocal
%SSH_CMD% "source ~/.cubrid.sh; cubrid server stop %1"
call :error_check %ERRORLEVEL% "stop"
%SSH_CMD% "source ~/.cubrid.sh; cubrid deletedb %1 && if [ -n '%2' ]; then rm -rf ~/'%2'; fi"
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
endlocal
exit /b

:runNormalCases
echo #####mv _16_largedata_longtime from the path PHP/php/php#####
move php\_16_largedata_longtime .
echo ##### start run test cases from _01_schema to _15_newLob#####
%RUN_TESTS% php
echo ##### finished #####
move _16_largedata_longtime php
exit /b

:runLargeDataCases
echo #####start to run test cases about large data#####
%RUN_TESTS% php\_16_largedata_longtime
echo #####finished#####
exit /b

:runAll
echo #####start to run all test cases : %php%#####
echo #####======%PHP_VER%_%PHP_FLAVOR%=======#####
powershell -NoProfile -Command "& { %RUN_TESTS% php } *>&1 | Tee-Object -FilePath 'php_tests2_win_%PHP_VER%_%PHP_FLAVOR%.log'"
echo #####finished#####
exit /b
