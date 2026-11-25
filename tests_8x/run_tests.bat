@echo off
rem
rem Run the tests with the extension built by ..\build_win.ps1 <ver>.
rem
rem   run_tests.bat [target] [run-tests.php options] [tests...]
rem       no test given: all tests
rem   run_tests.bat [target] --php script.php [args...]
rem       run a PHP script with the extension loaded
rem
rem   target: 84, 84-ts, 85, 85-ts (84 = 84-nts)
rem       Uses the PHP downloaded by build_win.ps1
rem       (..\build_win\downloads\php-<x.y>.*-[nts-]Win32-vs17-x64\php.exe).
rem       Without a target, PHP_BIN or php.exe in PATH is used.
rem
rem Environment
rem   PHP_BIN      PHP binary or its directory, e.g. D:\PHP\php-8.5.8-Win32-vs17-x64
rem   CUBRID_EXT   extension to test (default: ..\build_win\<ver>-<ts|nts>\modules\php_cubrid.dll,
rem                <ver> = 84, 85 and ts/nts from PHP_BIN)
rem   CUBRID_TEST_HOST  CUBRID broker host (default: test-db-server, see connect.inc)
rem
rem Examples
rem   run_tests.bat 85
rem   run_tests.bat 84-ts cubrid_connect.phpt cubrid_fetch.phpt
rem   set "PHP_BIN=D:\PHP\php-8.5.8-Win32-vs17-x64"
rem   run_tests.bat
rem   run_tests.bat 85 --php -r "var_dump(phpversion('cubrid'));"

setlocal
cd /d "%~dp0"
for %%r in ("%~dp0..") do set "ROOT=%%~fr"

rem ---- optional target ------------------------------------------------------
set "TGT_VER="
set "TGT_FLAVOR=nts"
for %%v in (84 85) do (
    if /i "%~1"=="%%v"     set "TGT_VER=%%v"
    if /i "%~1"=="%%v-nts" set "TGT_VER=%%v"
    if /i "%~1"=="%%v-ts"  (set "TGT_VER=%%v" & set "TGT_FLAVOR=ts")
)
if not defined TGT_VER goto :find_php
shift

set "XY=%TGT_VER:~0,1%.%TGT_VER:~1%"
set "DL=%ROOT%\build_win\downloads"
set "PHP_BIN="
rem Newest download wins when several versions are present.
if "%TGT_FLAVOR%"=="nts" (
    for /f "delims=" %%d in ('dir /b /ad /o:d "%DL%\php-%XY%.*-nts-Win32-vs17-x64" 2^>nul') do (
        if exist "%DL%\%%d\php.exe" set "PHP_BIN=%DL%\%%d\php.exe"
    )
) else (
    for /f "delims=" %%d in ('dir /b /ad /o:d "%DL%\php-%XY%.*-Win32-vs17-x64" 2^>nul ^| findstr /v /i /c:"-nts-"') do (
        if exist "%DL%\%%d\php.exe" set "PHP_BIN=%DL%\%%d\php.exe"
    )
)
if not defined PHP_BIN (
    echo [ERROR] PHP %XY% %TGT_FLAVOR% not found in %DL%. 1>&2
    if "%TGT_FLAVOR%"=="ts" (echo         Run '..\build_win.ps1 %TGT_VER% -ThreadSafe' first. 1>&2) else (echo         Run '..\build_win.ps1 %TGT_VER%' first. 1>&2)
    exit /b 1
)

rem ---- PHP binary -----------------------------------------------------------
:find_php
if not defined PHP_BIN set "PHP_BIN=php.exe"
if exist "%PHP_BIN%\php.exe" set "PHP_BIN=%PHP_BIN%\php.exe"
if not exist "%PHP_BIN%" for %%p in ("%PHP_BIN%") do set "PHP_BIN=%%~$PATH:p"
if not defined PHP_BIN (
    echo [ERROR] php not found. Give a target ^(84, 85-ts, ...^) or set PHP_BIN. 1>&2
    exit /b 1
)
for %%p in ("%PHP_BIN%") do set "PHP_BIN=%%~fp"

rem for /f mangles the PHP code, so read the output through a file.
rem "851" = PHP 8.5 TS, "840" = PHP 8.4 NTS
set "PHP_INFO="
set "INFO_FILE=%TEMP%\cubrid_run_tests_%RANDOM%.txt"
"%PHP_BIN%" -n -r "echo PHP_MAJOR_VERSION * 100 + PHP_MINOR_VERSION * 10 + PHP_ZTS;" > "%INFO_FILE%" 2>nul
set /p PHP_INFO=<"%INFO_FILE%"
del "%INFO_FILE%" 2>nul
if not defined PHP_INFO (
    echo [ERROR] Cannot run %PHP_BIN% 1>&2
    exit /b 1
)
set "PHP_VER=%PHP_INFO:~0,2%"
set "PHP_FLAVOR=nts"
if "%PHP_INFO:~2,1%"=="1" set "PHP_FLAVOR=ts"

rem ---- extension ------------------------------------------------------------
if not defined CUBRID_EXT set "CUBRID_EXT=%ROOT%\build_win\%PHP_VER%-%PHP_FLAVOR%\modules\php_cubrid.dll"
if exist "%CUBRID_EXT%" goto :ext_found
set "TS_OPT="
if "%PHP_FLAVOR%"=="ts" set "TS_OPT= -ThreadSafe"
echo [ERROR] %CUBRID_EXT% not found. Run '..\build_win.ps1 %PHP_VER%%TS_OPT%' first. 1>&2
exit /b 1
:ext_found
for %%f in ("%CUBRID_EXT%") do (
    set "CUBRID_EXT=%%~ff"
    set "EXT_DIR=%%~dpf"
)
rem php_cubrid.dll needs cascci.dll, which build_win.ps1 puts next to it.
set "PATH=%EXT_DIR%;%PATH%"

echo PHP       : %PHP_BIN% (%PHP_VER%, %PHP_FLAVOR%)
echo Extension : %CUBRID_EXT%

rem ---- --php mode -----------------------------------------------------------
if not "%~1"=="--php" goto :collect
shift
set "ARGS="
:php_args
if "%~1"=="" goto :run_php
set ARGS=%ARGS% %1
shift
goto :php_args
:run_php
"%PHP_BIN%" -n -d extension="%CUBRID_EXT%" %ARGS%
exit /b %ERRORLEVEL%

rem ---- run-tests.php --------------------------------------------------------
:collect
rem Run the default test set when no test path is given.
set "ARGS="
set "HAS_TARGET="
:test_args
if "%~1"=="" goto :run_tests
if exist "%~1" set "HAS_TARGET=1"
set ARGS=%ARGS% %1
shift
goto :test_args
:run_tests
set "TARGETS=."
if defined HAS_TARGET set "TARGETS="

set "NO_COLOR=1"
rem run-tests.php prefers TEST_PHP_*_EXECUTABLE from the environment over the
rem binaries next to -p, so a global setting (e.g. an NTS php-cgi.exe) would
rem load the extension into a PHP of another version/flavor. Pin them to PHP_BIN.
for %%p in ("%PHP_BIN%") do set "PHP_DIR=%%~dpp"
set "TEST_PHP_EXECUTABLE=%PHP_BIN%"
set "TEST_PHP_CGI_EXECUTABLE="
set "TEST_PHPDBG_EXECUTABLE="
if exist "%PHP_DIR%php-cgi.exe" set "TEST_PHP_CGI_EXECUTABLE=%PHP_DIR%php-cgi.exe"
if exist "%PHP_DIR%phpdbg.exe"  set "TEST_PHPDBG_EXECUTABLE=%PHP_DIR%phpdbg.exe"
powershell -NoProfile -Command "& { "%PHP_BIN%" run-tests.php -q -n -d extension="%CUBRID_EXT%" -p "%PHP_BIN%" %ARGS% %TARGETS% } *>&1 | Tee-Object -FilePath 'php_tests2_win_%PHP_VER%_%PHP_FLAVOR%.log'"
exit /b %ERRORLEVEL%
