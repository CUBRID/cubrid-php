Executing steps:

0. build the extension for the PHP to test (in the parent directory):
   "./build.sh 83", "./build.sh 84" or "./build.sh 85" -> build_linux/<ver>/modules/cubrid.so
   choseRunCases.sh loads build_linux/<ver>/modules/cubrid.so of the PHP version given by phppath.
1. if you want to run test cases about large data, the execution command is "sh choseRunCases.sh [phppath] -L"
2. if you want to test cases about normal, the execution command is "sh choseRunCases.sh [phppath] -S".
3. if you want to all test cases, the execution command is "sh choseRunCases.sh [phppath]".

phppath is the directory of the php binary (e.g. ~/php/8.4/bin); php in PATH is used if omitted.
A single test file or directory can be given after -S/-L: "sh choseRunCases.sh ~/php/8.4/bin -S php/_01_schema".
Set CUBRID_TEST_HOST to override the server host in connect.inc (default: test-db-server).
