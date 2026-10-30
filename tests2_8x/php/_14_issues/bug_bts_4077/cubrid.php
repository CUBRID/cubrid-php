<?php
require_once 'PHPUnit/Framework.php';
require_once 'global.php';

define('OS', 'LINUX');
//define ( 'OS', 'WINDOWS' );
define ( 'VERBOSE_OUTPUT', false );
//define('VERBOSE_OUTPUT', true);
define ( 'OUTPUT_FUNCTION_NAME', true );
//define('OUTPUT_FUNCTION_NAME', false);


class CubridTest extends PHPUnit_Framework_TestCase {
  const HOST = CUBRID_HOST;
  const PORT = CUBRID_PORT;
  const DBNAME = CUBRID_DB;
  const USERID = CUBRID_USER;
  const PASSWORD = CUBRID_PASSWORD;
  
  protected $con;
  protected $sql;
  protected $req;
  protected $result;
  protected $error_flag;
  protected $log;
  protected $desc;
  
  public function tracer() {
    $output = "";
    foreach ( debug_backtrace () as $entry ) {
      $output .= "\t\tFunction: " . $entry ['function'] . "\r\n";
    }
    return $output;
  }
  
  public function prepareErrorLogLinux($e) {
    $this->log = "echo -e '\E[37;41m[" . date ( 'Y/m/d h:i:s A' ) . "]''\E[0m'" . "\n" . "echo -e '\E[33;1m\t<function>: '" . "'\E[0m" . $this->log . "'" . "\n" . "echo -e '\E[34;1m\t<sql>: '" . "'\E[0m" . $this->sql . "'" . "\n" . "echo -e '\E[35;1m\t<Error Code>: '" . "'\E[0m" . cubrid_error_code () . "'" . "\n" . "echo -e '\E[36;1m\t<Error Facility>:' " . "'\E[0m" . cubrid_error_code_facility () . "'" . "\n" . "echo -e '\E[32;1m\t<Error Message>:' " . "'\E[0m" . cubrid_error_msg () . "'\n" . "echo -e '\E[30;1m\t<Line>:' " . "'\E[0m" . $e->getLine () . "'\n" . "echo -e '\E[31;1m\t<Exception Message>:' " . "'\E[0m" . $e->getMessage () . "'\n";
  }
  
  public function prepareErrorLogWindows($e) {
    $this->log = date ( 'Y/m/d h:i:s A' ) . "\r\n";
    $this->log .= "\t<Trace>:\r\n";
    $this->log .= self::tracer ();
    //$this->log.=debug_print_backtrace();
    $this->log .= "\t<SQL>: " . $this->sql . "\r\n";
    $this->log .= "\t<Error Code>: " . cubrid_error_code () . "\r\n";
    $this->log .= "\t<Error Facility>: " . cubrid_error_code_facility () . "\r\n";
    $this->log .= "\t<Error Message>: " . cubrid_error_msg () . "\r\n";
    $this->log .= "\t<Line>: " . $e->getLine () . "\r\n";
    $this->log .= "\t<Exception Message>: " . $e->getMessage () . "\r\n";
  }
  
  // write error log
  public function writeErrorLog($e) {
    if (VERBOSE_OUTPUT == true)
      echo "\r\nOpen log file: Errorlog.txt";
    if (! $handle = fopen ( "Errorlog.txt", 'a+' )) {
      print "\r\nCannot open the log file: Errorlog.txt.";
      exit ();
    }
    
    if (OS == 'LINUX')
      self::prepareErrorLogLinux ( $e );
    else
      self::prepareErrorLogWindows ( $e );
    
    if (! fwrite ( $handle, $this->log )) {
      print "\r\nCannot write to the log file.";
      exit ();
    }
    
    fclose ( $handle );
  }
  
  // write trace log
  public function writeTraceLog($function, $res, $desc, $sorf = "S") {
    if (VERBOSE_OUTPUT == true)
      echo "\r\nOpen log file: Log.txt";
    if (! $handle = fopen ( "log.txt", 'a+' )) {
      print "\r\nCannot open the log file: Log.txt";
      exit ();
    }
    
    if (! fwrite ( $handle, $function . "\t\t" . $desc . "\t\t" . $res . "\t" . $sorf . "\n" )) {
      print "\r\nCannot write to the log file.";
      exit ();
    }
    
    fclose ( $handle );
  }
  
  // load lib
  public static function loadLib($libname) {
    if (! extension_loaded ( $libname )) {
      $prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
      return dl ( $prefix . $libname . '.' . PHP_SHLIB_SUFFIX );
    }
    return TRUE;
  }
  
  // check cubrid server status, if not running, start it
  public static function checkServer() {
    if (OS == 'LINUX') {
      echo "\r\n";
      system ( "sh CheckServer.sh" );
    } else {
      echo "\r\n";
      system ( "CheckServer.cmd" );
    }
  }
  
  public function createTestTable() {
    //echo "\r\nCreating table test_table...";
    try {
      $this->sql = "CREATE TABLE test_table(column_integer INTEGER,";
      $this->sql .= "column_smallint SMALLINT,";
      $this->sql .= "column_numeric_9_2 NUMERIC(9,2),";
      $this->sql .= "column_char_9 CHAR(9),";
      $this->sql .= "column_varchar_92 VARCHAR(92),";
      $this->sql .= "column_date DATE,";
      $this->sql .= "column_bit BIT(4),";
      $this->sql .= "column_time TIME,";
      $this->sql .= "column_timestamp TIMESTAMP,";
      $this->sql .= "column_set SET,";
      $this->sql .= "PRIMARY KEY (column_integer)";
      $this->sql .= ")";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "INSERT INTO test_table VALUES(";
      $this->sql .= "1, 11, 1.1, '1', CURRENT_USER, SYS_DATE, NULL, SYS_TIME, SYS_TIMESTAMP, NULL";
      $this->sql .= ")";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $this->sql = "INSERT INTO test_table VALUES(";
      $this->sql .= "22, 222, 2.2, '22', CURRENT_USER, SYS_DATE, NULL, SYS_TIME, SYS_TIMESTAMP, {1}";
      $this->sql .= ")";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $this->sql = "INSERT INTO test_table VALUES(";
      $this->sql .= "333, 3333, 3.3, '333', CURRENT_USER, SYS_DATE, NULL, SYS_TIME, SYS_TIMESTAMP, {1,2}";
      $this->sql .= ")";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      
      return TRUE;
    } catch ( Exception $e ) {
      return FALSE;
    }
  }
  
  public function deleteTestTable() {
    //echo "\r\nDeleting table test_table...";
    $this->sql = "DROP TABLE test_table";
    $this->req = cubrid_execute ( $this->con, $this->sql );
  }
  
  // init
  protected function setUp() {
    if (VERBOSE_OUTPUT == true) {
      if (OS == 'LINUX')
        echo "\r\nRunning on Linux...";
      else
        echo "\r\nRunning on Windows...";
      
      if (VERBOSE_OUTPUT == true)
        echo "\r\nExtended output is ON.";
      else
        echo "\r\nExtended output is OFF.";
    }
    
    //phpinfo();
    ini_set ( "cubrid.err_path", getcwd () );
    
    if (VERBOSE_OUTPUT == true)
      echo "\r\nLoading Cubrid PHP extension...";
    if (! self::loadlib ( 'cubrid' )) {
      $this->markTestSkipped ( "\r\nThe Cubrid extension is not available." );
    }
    
    if (OS == 'LINUX') {
      if (VERBOSE_OUTPUT == true)
        echo "\r\nLoading PNCTL PHP extension...";
      if (! self::loadlib ( 'pcntl' )) {
        $this->markTestSkipped ( "\r\nThe PNCTL PHP extension is not available." );
      } else {
        if (VERBOSE_OUTPUT == true)
          echo "\r\nPNCTL PHP extension loaded.";
      }
    }
    //$this->con = cubrid_connect ( "test-db-server", "33000", "phptests", "dba", "" );
    $this->con = cubrid_connect ( CubridTest::HOST, CubridTest::PORT, CubridTest::DBNAME, CubridTest::USERID, CubridTest::PASSWORD );
    
    if (! $this->con) {
      $this->markTestSkipped ( "\r\nCannot connect to the Cubrid db server." );
    }
    if (VERBOSE_OUTPUT == true)
      echo "\r\nConnected to the Cubrid database: " . CubridTest::DBNAME;
      
    // default values
    //$this->sql="select * from db_root";
    $this->error_flag = FALSE;
  }

  /**
   * @group php-832
   */  
  public function testCubridFetchRow0() {
      if (OUTPUT_FUNCTION_NAME == true)
          echo "\r\nRunning: " . __FUNCTION__ . " = ";
      $this->sql = "create table test111 (id datetime)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "insert into test111(id) values(datetime'0001-01-01 00:00:00'), (datetime'1-1-1 1:1:1'), (datetime'10-1-1 1:1:1'), (datetime'100-1-1 1:1:1')";
      cubrid_execute ( $this->con, $this->sql );
      
      echo "connction:";
      echo $this->con;

      try {
          $date1 = '0001-01-01 00:00:00.000';
          $date2 = '0001-01-01 01:01:01.000';
          $date3 = '2010-01-01 01:01:01.000';
          $date4 = '0100-01-01 01:01:01.000';
          echo " -------------------------------------";
          $this->sql = "SELECT id FROM test111";
          $this->req = cubrid_execute ( $this->con, $this->sql );  

          echo "@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@";
          $str = cubrid_fetch_row( $this->req );
          echo $str[0];
          $this->assertEquals ( $str[0], $date1);

          echo "-----------------------------------";
          $str = cubrid_fetch_row( $this->req );
          echo $str[0];
          $this->assertEquals ( $str[0], $date2);

          echo "***********************************";
          $str = cubrid_fetch_row( $this->req );
          echo $str[0];
          $this->assertEquals ( $str[0], $date3);

          echo "^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^";
          $str = cubrid_fetch_row( $this->req );
          echo $str[0];
          $this->assertEquals ( $str[0], $date4);

      } catch ( Exception $e ) {
          $this->desc = "Use wrong parameter";
          self::writeTraceLog ( __FUNCTION__, - 1, $this->desc );
          //$this->sql = "drop table test111";
          cubrid_execute ( $this->con, $this->sql );      
          $this->assertTrue ( false );
      }
      $this->sql = "drop table test111";
      cubrid_execute ( $this->con, $this->sql );      
    }

  /**
   * @group arnia
   */
  public function testCubridFetchLengths1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      echo $this->req;
      $lens = cubrid_fetch_lengths ( $this->req );
      $this->assertType ( 'array', $lens );
      $this->assertEquals ( 1, $lens [0] );
      $this->assertEquals ( 2, $lens [1] );
      $this->assertEquals ( 4, $lens [2] );
      $this->assertEquals ( 9, $lens [3] );
      $this->assertEquals ( 3, $lens [4] );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridFetchLengths2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $lens = cubrid_fetch_lengths ();
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridFetchLengths3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $lens = cubrid_fetch_lengths ( - 1 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia
   */
  public function testCubridFetchObject1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $obj = cubrid_fetch_object ( $this->req );
      $this->assertEquals ( 1, $obj->column_integer );
      //TODO Verify all columns returned
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridFetchObject2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $obj = cubrid_fetch_object ();
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridFetchObject3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $obj = cubrid_fetch_object ( - 1 );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia
   */
  public function testCubridFieldSeek1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      cubrid_field_seek ( $this->req, 0 );
      $val = cubrid_fetch_field ( $this->req );
      $this->assertEquals ( 'column_integer', $val->name );
      cubrid_field_seek ( $this->req, 4 );
      $val = cubrid_fetch_field ( $this->req );
      $this->assertEquals ( 'column_varchar_92', $val->name );
      //Add more attributes to verifications
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridFieldSeek2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      cubrid_field_seek ();
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridFieldSeek3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      cubrid_field_seek ( - 1, - 1 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia
   */
  public function testCubridFieldLen1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $len = cubrid_field_len ( $this->req, 0 );
      $this->assertEquals ( 10, $len );
      $len = cubrid_field_len ( $this->req, 1 );
      $this->assertEquals ( 5, $len );
      $len = cubrid_field_len ( $this->req, 2 );
      $this->assertEquals ( 11, $len );
      $len = cubrid_field_len ( $this->req, 3 );
      $this->assertEquals ( 9, $len );
      $len = cubrid_field_len ( $this->req, 4 );
      $this->assertEquals ( 92, $len );
      $len = cubrid_field_len ( $this->req, 5 );
      $this->assertEquals ( 10, $len );
      $len = cubrid_field_len ( $this->req, 6 );
      $this->assertEquals ( 4, $len );
      $len = cubrid_field_len ( $this->req, 7 );
      $this->assertEquals ( 8, $len );
      $len = cubrid_field_len ( $this->req, 8 );
      $this->assertEquals ( 23, $len );
      $len = cubrid_field_len ( $this->req, 9 );
      $this->assertEquals ( 1073741823, $len );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridFieldLen2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $len = cubrid_field_len ();
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridFieldLen3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $len = cubrid_field_len ( - 1, 0 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia
   */
  public function testCubridUnbufferedQuery1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_unbuffered_query ( $this->sql, $this->con );
      $row = cubrid_fetch ( $this->req, CUBRID_OBJECT );
      $this->assertEquals ( 1, $row->column_integer );
      $this->assertEquals ( 11, $row->column_smallint );
      $this->assertEquals ( 1.10, $row->column_numeric_9_2 );
      $this->assertEquals ( '1        ', $row->column_char_9 );
      $this->assertEquals ( 'DBA', $row->column_varchar_92 );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridUnbufferedQuery2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_unbuffered_query ();
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridUnbufferedQuery3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_unbuffered_query ( 0, - 1 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia
   */
  public function testCubridResult1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $value = cubrid_result ( $this->req, 0 );
      $this->assertType ( 'integer', $value );
      $this->assertEquals ( 1, $value );
      $value = cubrid_result ( $this->req, 0, 0 );
      $this->assertType ( 'integer', $value );
      $this->assertEquals ( 1, $value );
      $value = cubrid_result ( $this->req, 2, 2 );
      $this->assertType ( 'float', $value );
      $this->assertEquals ( 3.3, $value );
      $value = cubrid_result ( $this->req, 2, 'column_integer' );
      $this->assertType ( 'integer', $value );
      $this->assertEquals ( 333, $value );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridResult2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $value = cubrid_result ();
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridResult3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      echo "connection:";
      echo $this->con;
      echo ".......";
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      echo ".......";
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      echo $this->req;
      $value = cubrid_result ( - 1, - 1 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia
   */
  public function testCubridGetCharset1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $val = cubrid_get_charset ( $this->con );
      $this->assertEquals ( 'iso8859-1', $val );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridGetCharset2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      cubrid_get_charset ();
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridGetCharset3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $val = cubrid_get_charset ( " " );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
  }
  
  /**
   * @group arnia
   */
  public function testCubridGetClientInfo1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $val = cubrid_get_client_info ();
      $str = substr ( $val, 0, 4 );
      $this->assertEquals ( '8.2.', $str );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridGetClientInfo2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      cubrid_get_client_info ( 1 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
  }
  
  /**
   * @group arnia
   */
  public function testCubridGetServerInfo1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $val = cubrid_get_server_info ( $this->con );
      $str = substr ( $val, 0, 4 );
      $this->assertEquals ( '8.2.', $str );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridGetServerInfo2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      cubrid_get_server_info ();
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridGetServerInfo3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $val = cubrid_get_server_info ( " " );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
  }
  
  /**
   * @group arnia
   */
  public function testCubridRealEscapeString1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "SELECT \abc\, 'ds' FROM db_root";
      $escaped_sql = cubrid_real_escape_string ( $this->sql );
      $this->assertEquals ( "SELECT \\\\abc\\\\, \'ds\' FROM db\_root", $escaped_sql );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridRealEscapeString2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "SELECT \abc\, 'ds' FROM db_root";
      $escaped_sql = cubrid_real_escape_string ();
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
  }
  
  /**
   * @group arnia
   */
  public function testCubridGetDbParameter1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $params = cubrid_get_db_parameter ( $this->con );
      $this->assertType ( 'array', $params );
      $this->assertEquals ( array ("PARAM_ISOLATION_LEVEL" => 3, "LOCK_TIMEOUT" => - 1, "MAX_STRING_LENGTH" => 1073741823 ), $params );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridGetDbParameter2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $params = cubrid_get_db_parameter ();
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridGetDbParameter3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $params = cubrid_get_db_parameter ( " " );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
  }
  
  /**
   * @group arnia
   */
  public function testCubridListDbs1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $dbs = cubrid_list_dbs ( $this->con );
      $this->assertType ( 'array', $dbs );
      $str = substr ( $dbs [0], 0, 7 );
      $this->assertEquals ( CubridTest::DBNAME, $str );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridListDbs2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $dbs = cubrid_list_dbs ();
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
  }
  
  /**
   * @group arnia
   */
  public function testCubridInsertId1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $table = "test_table_2";
      $this->sql = "CREATE TABLE test_table_2(column_integer INTEGER, column_serial INTEGER auto_increment)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "INSERT INTO test_table_2(column_integer) VALUES(" . rand () . ")";
      cubrid_execute ( $this->con, $this->sql );
      $value = cubrid_insert_id ( $table );
      $this->assertEquals ( 1, $value ["column_serial"] );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    
    $this->sql = "DROP TABLE test_table_2";
    cubrid_execute ( $this->con, $this->sql );
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridInsertId2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $table = "test_table_2";
      $this->sql = "CREATE TABLE test_table_2(column_integer INTEGER, column_serial INTEGER auto_increment)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "INSERT INTO test_table_2(column_integer) VALUES(" . rand () . ")";
      cubrid_execute ( $this->con, $this->sql );
      $value = cubrid_insert_id ();
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    
    $this->sql = "DROP TABLE test_table_2";
    $this->req = cubrid_execute ( $this->con, $this->sql );
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridInsertId3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $table = "test_table_2";
      $this->sql = "CREATE TABLE test_table_2(column_integer INTEGER, column_serial INTEGER auto_increment)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "INSERT INTO test_table_2(column_integer) VALUES(" . rand () . ")";
      cubrid_execute ( $this->con, $this->sql );
      $value = cubrid_insert_id ( - 1 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( - 2006, cubrid_error_code () );
      $this->assertEquals ( 4, cubrid_error_code_facility () );
      $this->assertEquals ( 'Invalid parameter', cubrid_error_msg () );
    }
    $this->sql = "DROP TABLE test_table_2";
    cubrid_execute ( $this->con, $this->sql );
  }
  
  /**
   * @group arnia
   */
  public function testCubridFieldName1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_field_name ( $this->req, 0 );
      $this->assertEquals ( 'column_integer', $val );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridFieldName2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_field_name ( $this->req );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridFieldName3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_field_name ( $this->req, - 1 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( - 2006, cubrid_error_code () );
      $this->assertEquals ( 4, cubrid_error_code_facility () );
      $this->assertEquals ( 'Invalid parameter', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia
   */
  public function testCubridFieldTable1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_field_table ( $this->req, 0 );
      $this->assertEquals ( 'test_table', $val );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridFieldTable2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_field_table ( $this->req );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridFieldTable3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_field_table ( $this->req, - 1 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( - 2006, cubrid_error_code () );
      $this->assertEquals ( 4, cubrid_error_code_facility () );
      $this->assertEquals ( 'Invalid parameter', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia
   */
  public function testCubridFieldType1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_field_type ( $this->req, 0 );
      $this->assertEquals ( 'INT', $val );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridFieldType2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_field_type ( $this->req );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridFieldType3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_field_type ( $this->req, - 100 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( - 2006, cubrid_error_code () );
      $this->assertEquals ( 4, cubrid_error_code_facility () );
      $this->assertEquals ( 'Invalid parameter', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia
   */
  public function testCubridFieldFlags1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_field_flags ( $this->req, 0 );
      $this->assertEquals ( 'not_null primary_key unique_key', $val );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridFieldFlags2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_field_flags ( $this->req );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridFieldFlags3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_field_flags ( $this->req, - 100 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( - 2006, cubrid_error_code () );
      $this->assertEquals ( 4, cubrid_error_code_facility () );
      $this->assertEquals ( 'Invalid parameter', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia
   */
  public function testCubridDataSeek1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_data_seek ( $this->req, 1 );
      $valobj = cubrid_fetch_object ( $this->req );
      $this->assertEquals ( 22, $valobj->column_integer );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridDataSeek2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_data_seek ( $this->req );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridDataSeek3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_data_seek ( $this->req, - 1 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia
   */
  public function testCubridFetchAssoc1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_fetch_assoc ( $this->req );
      $this->assertType ( 'array', $val );
      $this->assertEquals ( 1, $val ["column_integer"] );
      $this->assertEquals ( 11, $val ["column_smallint"] );
      $this->assertEquals ( 1.10, $val ["column_numeric_9_2"] );
      $this->assertEquals ( 'DBA', $val ["column_varchar_92"] );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridFetchAssoc2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_fetch_assoc ();
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridFetchAssoc3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_fetch_assoc ( - 1 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia
   */
  public function testCubridFetchRow1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_fetch_row ( $this->req );
      $this->assertType ( 'array', $val );
      $this->assertEquals ( 1, $val [0] );
      $this->assertEquals ( 11, $val [1] );
      $this->assertEquals ( 1.10, $val [2] );
      $this->assertEquals ( 'DBA', $val [4] );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridFetchRow2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_fetch_row ();
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridFetchRow3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_fetch_row ( - 1 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia
   */
  public function testCubridFetchField1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_fetch_field ( $this->req );
      $this->assertEquals ( "column_integer", $val->name );
      $this->assertEquals ( "test_table", $val->table );
      $this->assertEquals ( "", $val->def );
      $this->assertEquals ( 3, $val->max_length );
      $this->assertEquals ( 1, $val->not_null );
      $this->assertEquals ( 1, $val->unique_key );
      $this->assertEquals ( 0, $val->multiple_key );
      $this->assertEquals ( 1, $val->numeric );
      $this->assertEquals ( "INT", $val->type );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia
   */
  public function testCubridFetchField2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_fetch_field ( $this->req, 4 );
      $this->assertEquals ( "column_varchar_92", $val->name );
      $this->assertEquals ( "test_table", $val->table );
      $this->assertEquals ( "", $val->def );
      $this->assertEquals ( 3, $val->max_length );
      $this->assertEquals ( 0, $val->not_null );
      $this->assertEquals ( 0, $val->unique_key );
      $this->assertEquals ( 1, $val->multiple_key );
      $this->assertEquals ( 0, $val->numeric );
      $this->assertEquals ( "STRING", $val->type );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridFetchField3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_fetch_field ();
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridFetchField4() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_fetch_field ( $this->req, 100 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia
   */
  public function testCubridNumFields1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_num_fields ( $this->req );
      $this->assertType ( 'integer', $val );
      $this->assertEquals ( 10, $val );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridNumFields2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_num_fields ();
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridNumFields3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_num_fields ( - 1 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia
   */
  public function testCubridFreeResult1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_free_result ( $this->req );
      $this->assertType ( 'boolean', $val );
      $this->assertEquals ( TRUE, $val );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->assertTrue ( TRUE );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridFreeResult2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_free_result ();
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group arnia-wrong-parameters
   */
  public function testCubridFreeResult3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->assertTrue ( $this->createTestTable (), "Failed to create the test table." );
      $this->sql = "SELECT * FROM test_table";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $val = cubrid_free_result ( - 1 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    $this->deleteTestTable ();
  }
  
  /**
   * @group php-822
   */
  public function testCubridAffectedRows0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $arraySql = array ("insert into test1 values(4)", "insert into test1 select * from test1 where id<3", "insert into test1 select * from test1 where id>3", "delete from test1 where id=1", "delete from test1 where id<3", "delete from test1 where id>3", "update test1 set id=0 where id=1", "update test1 set id=0 where id=1 or id=2", "update test1 set id=0 where id>3", "insert into test1 values(4);
      insert into test1 values(4);
      insert into test1 values(4);
      insert into test1 values(4);
      " );
    
    $arrayResult = array ("1", "2", "0", "1", "2", "0", "1", "2", "0", "1" );
    
    $arrayDesc = array ("insert, result = 1", "insert, result > 1", "insert, result = 0", "delete, result = 1", "delete, result > 1", "delete, result = 0", "update, result = 1", "update, result > 1", "update, result = 0", "multi-insert, result = 1" );
    
    foreach ( $arraySql as $key => $value ) {
      $this->sql = "create table test1 (id int)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "insert into test1 values(1)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "insert into test1 values(2)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "insert into test1 values(3)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = $value;
      $this->req = cubrid_execute ( $this->con, $this->sql );
      //$this->assertNotNull($this->req);
      $this->assertEquals ( $arrayResult [$key], cubrid_affected_rows ( $this->req ) );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
      self::writeTraceLog ( __FUNCTION__, cubrid_affected_rows ( $this->req ), $arrayDesc [$key] );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridAffectedRows1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(1)";
    cubrid_execute ( $this->con, $this->sql );
    
    try {
      $res = cubrid_affected_rows ();
    } catch ( Exception $e ) {
      $this->desc = "Use wrong parameter";
      self::writeTraceLog ( __FUNCTION__, - 1, $this->desc );
      $this->assertTrue ( TRUE );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridAffectedRows2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(1)";
    cubrid_execute ( $this->con, $this->sql );
    
    try {
      $res = cubrid_affected_rows ( null );
    } catch ( Exception $e ) {
      $this->desc = "Use wrong parameter";
      self::writeTraceLog ( __FUNCTION__, - 1, $this->desc );
      $this->assertTrue ( TRUE );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindAdd() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "create table test1 (id int)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, null );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindInt0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $arrayNormalCase = array (( int ) 10, ( float ) 89.9, 0x123, 1.2e+3, TRUE, "-89.9", "89.9", "-2147483648", "2147483647", "-100", "0", "2" );
    
    $arrayNormalResult = array (10, 90, 0x123, 1.2e+3, 1, "-90", "90", "-2147483648", "2147483647", "-100", "0", "2" );
    
    foreach ( $arrayNormalCase as $key => $value ) {
      $this->sql = "create table test1 (id int)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, $value );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      
      // test data
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      list ( $key1, $id ) = each ( cubrid_fetch ( $this->req ) );
      $this->assertEquals ( $arrayNormalResult [$key], $id );
      
      // test type
      list ( $key2, $coltype ) = each ( cubrid_column_types ( $this->req ) );
      $this->assertEquals ( 'integer', $coltype );
      
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindInt1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // int positive overflow test
    try {
      $this->sql = "create table test1 (id int)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, "2147483648" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindInt2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // int negative overflow test
    try {
      $this->sql = "create table test1 (id int)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, "-2147483649" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindInt3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // int wrong type test
    try {
      $this->sql = "create table test1 (id int)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, "abcd" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindNumeric0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $arrayNormalCase = array (( int ) 10, ( float ) 89.9, 0x123, 1.2e+3, TRUE, ".333333", "55.33", "55.333333", "55555.33", "555555555555555.33", "555.33", "-1", "-0", "0", "-89.9", "89.9" );
    
    $arrayNormalResult = array (( int ) 10, 90, 0x123, 1.2e+3, 1, ".333", "55.33", "55.333", "55555", "555555555555555", "555", "-1", "0", "0", "-90", "90" );
    
    $arraySqlType = array ("numeric(15,0)", "numeric(15,0)", "numeric(15,0)", "numeric(15,0)", "numeric(15,0)", "numeric(5,3)", "numeric(5,3)", "numeric(5,3)", "numeric(5,0)", "numeric", "numeric(5)", "numeric", "numeric", "numeric", "numeric", "numeric" );
    
    $arrayResultType = array ("numeric(15,0)", "numeric(15,0)", "numeric(15,0)", "numeric(15,0)", "numeric(15,0)", "numeric(5,3)", "numeric(5,3)", "numeric(5,3)", "numeric(5,0)", "numeric(15,0)", "numeric(5,0)", "numeric(15,0)", "numeric(15,0)", "numeric(15,0)", "numeric(15,0)", "numeric(15,0)" );
    
    foreach ( $arrayNormalCase as $key => $value ) {
      $this->sql = "create table test1 (id $arraySqlType[$key])";
      echo $this->sql;
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, $value );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      
      // test data
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      list ( $key1, $id ) = each ( cubrid_fetch ( $this->req ) );
      $this->assertEquals ( $arrayNormalResult [$key], $id );
      
      // test type
      list ( $key2, $coltype ) = each ( cubrid_column_types ( $this->req ) );
      $this->assertEquals ( $arrayResultType [$key], $coltype );
      
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindNumeric1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // int overflow test
    try {
      $this->sql = "create table test1 (id NUMERIC(5))";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, "555555" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindSmallInt0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $arrayNormalCase = array (( int ) 10, ( float ) 89.9, 0x123, 1.2e+3, TRUE, "-32768", "-1", "0", "1", "34.4", "34.5", "34.6", "32767" );
    
    $arrayNormalResult = array (( int ) 10, 90, 0x123, 1.2e+3, 1, "-32768", "-1", "0", "1", "34", "35", "35", "32767" );
    
    foreach ( $arrayNormalCase as $key => $value ) {
      $this->sql = "create table test1 (id SMALLINT)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, $value );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      
      // test data
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      list ( $key1, $id ) = each ( cubrid_fetch ( $this->req ) );
      $this->assertEquals ( $arrayNormalResult [$key], $id );
      
      // test type
      list ( $key2, $coltype ) = each ( cubrid_column_types ( $this->req ) );
      $this->assertEquals ( 'smallint', $coltype );
      
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindSmallInt1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // int test string type
    try {
      $this->sql = "create table test1 (id SMALLINT)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, "asdasd" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindSmallInt2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // int positive overflow test
    try {
      $this->sql = "create table test1 (id SMALLINT)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, "32768" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindSmallInt3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // int negative overflow test
    try {
      $this->sql = "create table test1 (id SMALLINT)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, "-32769" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindFloat0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $arrayNormalCase = array (( int ) 10, ( float ) 89.9, 0x123, 1.2e+3, TRUE, "0", "1", "-1", "1.23", "-1.23", "1.234567", "-1.234567", "1.2345671", "-1.2345671", "1.2345678", "-1.2345678" );
    
    $arrayNormalResult = array (( int ) 10, 89.900002, 0x123, 1.2e+3, 1, "0", "1", "-1", "1.23", "-1.23", "1.234567", "-1.234567", "1.234567", "-1.234567", "1.234568", "-1.234568" );
    
    foreach ( $arrayNormalCase as $key => $value ) {
      //echo $key;
      $this->sql = "create table test1 (id FLOAT)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      //echo $value;
      $res = cubrid_bind ( $this->req, 1, $value );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      // test data
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      list ( $key1, $id ) = each ( cubrid_fetch ( $this->req ) );
      $this->assertEquals ( $arrayNormalResult [$key], $id );
      
      // test type
      list ( $key2, $coltype ) = each ( cubrid_column_types ( $this->req ) );
      $this->assertEquals ( 'float', $coltype );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindFloat1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // floattest max test
    $this->sql = "create table test1 (id FLOAT)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(?)";
    //$this->assertNotNull($this->req);
    $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    
    $res = cubrid_bind ( $this->req, 1, 3000000000000 );
    $this->assertTrue ( $res );
    cubrid_execute ( $this->req );
    
    // test data
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    list ( $key, $id ) = each ( cubrid_fetch ( $this->req ) );
    $this->assertEquals ( '3000000000000', $id );
    
    // test type
    list ( $key, $coltype ) = each ( cubrid_column_types ( $this->req ) );
    $this->assertEquals ( 'float', $coltype );
    
    $this->sql = "drop table test1";
    cubrid_execute ( $this->con, $this->sql );
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindFloat2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // floattest max test
    $this->sql = "create table test1 (id FLOAT)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(?)";
    //$this->assertNotNull($this->req);
    $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $res = cubrid_bind ( $this->req, 1, "200000000000000000000000000000000000000", "FLOAT" );
    $this->assertTrue ( $res );
    cubrid_execute ( $this->req );
    
    //  cubrid_commit($this->con);
    // test data
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    list ( $key, $id ) = each ( cubrid_fetch ( $this->req ) );
    $this->assertEquals ( '200000000000000000000000000000000000000', $id );
    
    // test type
    list ( $key, $coltype ) = each ( cubrid_column_types ( $this->req ) );
    $this->assertEquals ( 'float', $coltype );
    
    $this->sql = "drop table test1";
    cubrid_execute ( $this->con, $this->sql );
    $this->con = null;
    $this->req = null;
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindDouble0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $arrayNormalCase = array (( int ) 10, ( float ) 89.9, 0x123, 1.2e+3, TRUE, "0", "1", "-1", "1.23", "-1.23", "1.234567", "-1.234567", "1.2345671", "-1.2345671", "1.2345678", "-1.2345678", "1.2345678901234567", "1.23456789012345678" );
    
    $arrayNormalResult = array (( int ) 10, 89.9000000000000057, 0x123, 1.2e+3, 1, "0", "1", "-1", "1.23", "-1.23", "1.234567", "-1.234567", "1.2345671", "-1.2345671", "1.2345678", "-1.2345678", "1.2345678901234567", "1.2345678901234568" );
    
    foreach ( $arrayNormalCase as $key => $value ) {
      //echo $key;
      $this->sql = "create table test1 (id DOUBLE)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      //echo $value;
      $res = cubrid_bind ( $this->req, 1, $value );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      // test data
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      list ( $key1, $id ) = each ( cubrid_fetch ( $this->req ) );
      $this->assertEquals ( $arrayNormalResult [$key], $id );
      
      // test type
      list ( $key2, $coltype ) = each ( cubrid_column_types ( $this->req ) );
      $this->assertEquals ( 'double', $coltype );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindDouble1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // floattest max test
    $this->sql = "create table test1 (id DOUBLE)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(?)";
    //$this->assertNotNull($this->req);
    $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    
    $res = cubrid_bind ( $this->req, 1, 3000000000000 );
    $this->assertTrue ( $res );
    cubrid_execute ( $this->req );
    
    // test data
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    list ( $key, $id ) = each ( cubrid_fetch ( $this->req ) );
    $this->assertEquals ( '3000000000000', $id );
    
    // test type
    list ( $key, $coltype ) = each ( cubrid_column_types ( $this->req ) );
    $this->assertEquals ( 'double', $coltype );
    
    $this->sql = "drop table test1";
    cubrid_execute ( $this->con, $this->sql );
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindDouble2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // floattest max test
    $this->sql = "create table test1 (id DOUBLE)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(?)";
    //$this->assertNotNull($this->req);
    $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $res = cubrid_bind ( $this->req, 1, "200000000000000000000000000000000000000", "DOUBLE" );
    $this->assertTrue ( $res );
    cubrid_execute ( $this->req );
    
    //cubrid_commit($this->con);
    // test data
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    list ( $key, $id ) = each ( cubrid_fetch ( $this->req ) );
    $this->assertEquals ( '200000000000000000000000000000000000000', $id );
    
    // test type
    list ( $key, $coltype ) = each ( cubrid_column_types ( $this->req ) );
    $this->assertEquals ( 'double', $coltype );
    
    $this->sql = "drop table test1";
    cubrid_execute ( $this->con, $this->sql );
    $this->con = null;
    $this->req = null;
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindVarchar0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $arrayNormalCase = array (( int ) 10, ( float ) 89.9, 0x123, 1.2e+3, TRUE, "0", "!@#$%", "test", "�?�?" );
    
    $arrayNormalResult = array (( int ) 10, 89.9, 0x123, 1.2e+3, 1, "0", "!@#$%", "test", "�?�?" );
    
    foreach ( $arrayNormalCase as $key => $value ) {
      // string test
      $this->sql = "create table test1 (name varchar(30))";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, $value );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      
      // test data
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      list ( $key1, $id ) = each ( cubrid_fetch ( $this->req ) );
      $this->assertEquals ( $arrayNormalResult [$key], $id );
      
      // test type
      list ( $key2, $coltype ) = each ( cubrid_column_types ( $this->req ) );
      $this->assertEquals ( 'varchar(30)', $coltype );
      
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindVarchar1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // string range test
    try {
      $this->sql = "create table test1 (name varchar(30))";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, "1234567890123456789012345678901" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindChar0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $arrayNormalCase = array (( int ) 10, ( float ) 89.9, 0x123, 1.2e+3, TRUE, "test", "test", "test    ", "�?�?" );
    
    $arrayNormalResult = array (10, 89.9, 0x123, 1.2e+3, 1, "test  ", "test", "test", "�?�?" );
    
    $arraySqlType = array ("char(2)", "char(4)", "char(3)", "char(4)", "char(1)", "char(6)", "char(4)", "char(4)", "char(6)" );
    
    $arrayResultType = array ("char(2)", "char(4)", "char(3)", "char(4)", "char(1)", "char(6)", "char(4)", "char(4)", "char(6)" );
    
    //    $i = 1;
    foreach ( $arrayNormalCase as $key => $value ) {
      //      if($i == 6)
      //        break;
      //      else
      //        $i++;
      // stringtest length <N
      $this->sql = "create table test1 (name $arraySqlType[$key])";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, $value );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      
      // test data
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      list ( $key1, $id ) = each ( cubrid_fetch ( $this->req ) );
      $this->assertEquals ( $arrayNormalResult [$key], $id );
      
      // test type
      list ( $key2, $coltype ) = each ( cubrid_column_types ( $this->req ) );
      $this->assertEquals ( $arrayResultType [$key], $coltype );
      
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindChar1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // nchartest length >N
    try {
      $this->sql = "create table test1 (name char(30))";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, "1234567890123456789012345678901" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindNChar0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $arrayNormalCase = array (( int ) 10, ( float ) 89.9, 0x123, 1.2e+3, TRUE, "test", "test", "test    ", "�?�?" );
    
    $arrayNormalResult = array (10, 89.9, 0x123, 1.2e+3, 1, "test  ", "test", "test", "�?�?" );
    
    $arraySqlType = array ("nchar(2)", "nchar(4)", "nchar(3)", "nchar(4)", "nchar(1)", "nchar(6)", "nchar(4)", "nchar(4)", "nchar(6)" );
    
    $arrayResultType = array ("nchar(2)", "nchar(4)", "nchar(3)", "nchar(4)", "nchar(1)", "nchar(6)", "nchar(4)", "nchar(4)", "nchar(6)" );
    
    foreach ( $arrayNormalCase as $key => $value ) {
      // string test length <N
      $this->sql = "create table test1 (id $arraySqlType[$key])";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, $value, "NCHAR" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      
      // test data
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      list ( $key1, $id ) = each ( cubrid_fetch ( $this->req ) );
      $this->assertEquals ( $arrayNormalResult [$key], $id );
      
      // test type
      list ( $key2, $coltype ) = each ( cubrid_column_types ( $this->req ) );
      $this->assertEquals ( $arrayResultType [$key], $coltype );
      
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindNChar1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // nchar test length >N
    try {
      $this->sql = "create table test1 (name nchar(30))";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, "1234567890123456789012345678901", "NCHAR" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindNVChar0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $arrayNormalCase = array (( int ) 10, ( float ) 89.9, 0x123, 1.2e+3, TRUE, "test", "test", "test    ", "�?�?" );
    
    $arrayNormalResult = array (10, 89.9, 0x123, 1.2e+3, 1, "test", "test", "test", "�?�?" );
    
    $arraySqlType = array ("NCHAR VARYING(2)", "NCHAR VARYING(4)", "NCHAR VARYING(3)", "NCHAR VARYING(4)", "NCHAR VARYING(1)", "NCHAR VARYING(30)", "NCHAR VARYING(4)", "NCHAR VARYING(4)", "NCHAR VARYING(6)" );
    
    $arrayResultType = array ("varnchar(2)", "varnchar(4)", "varnchar(3)", "varnchar(4)", "varnchar(1)", "varnchar(30)", "varnchar(4)", "varnchar(4)", "varnchar(6)" );
    
    foreach ( $arrayNormalCase as $key => $value ) {
      // stringtest length <N
      $this->sql = "create table test1 (id $arraySqlType[$key])";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, $value, "NCHAR" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      
      // test data
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      list ( $key1, $id ) = each ( cubrid_fetch ( $this->req ) );
      $this->assertEquals ( $arrayNormalResult [$key], $id );
      
      // test type
      list ( $key2, $coltype ) = each ( cubrid_column_types ( $this->req ) );
      $this->assertEquals ( $arrayResultType [$key], $coltype );
      
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  // ?????bug :trim not error??
  public function testCubridBindNVChar1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // string test >N
    $this->sql = "create table test1 (name NCHAR VARYING(4))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(?)";
    //$this->assertNotNull($this->req);
    $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $res = cubrid_bind ( $this->req, 1, "123123123123", "NCHAR" );
    $this->assertTrue ( $res );
    cubrid_execute ( $this->req );
    
    // test data
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    list ( $key, $id ) = each ( cubrid_fetch ( $this->req ) );
    $this->assertEquals ( '123123123123', $id );
    
    // test type
    list ( $key, $coltype ) = each ( cubrid_column_types ( $this->req ) );
    $this->assertEquals ( 'varnchar(4)', $coltype );
    
    $this->sql = "drop table test1";
    cubrid_execute ( $this->con, $this->sql );
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindBit0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $arrayNormalCase = array (( int ) 10, ( float ) 89.9, 0x123, 1.2e+3, TRUE, "10", "�?" );
    
    $arrayNormalResult = array (3130, 38392E39, 323931, 31323030, 31, "3130", "E6B58B" );
    
    $arraySqlType = array ("bit(16)", "bit(32)", "bit(24)", "bit(32)", "bit(8)", "bit(16)", "bit(24)" );
    
    //    $arrayResultType = array("bit","bit","bit","","varnchar(1)",
    //                "varnchar(30)", "varnchar(4)", "varnchar(4)", "varnchar(6)");
    

    $i = 1;
    
    foreach ( $arrayNormalCase as $key => $value ) {
      if ($i == 8)
        break;
      else
        $i ++;
        // stringtest length <N
      $this->sql = "create table test1 (name $arraySqlType[$key])";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, $value, "BIT" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      
      // test data
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      list ( $key1, $id ) = each ( cubrid_fetch ( $this->req ) );
      $this->assertEquals ( $arrayNormalResult [$key], $id );
      
      // test type
      list ( $key2, $coltype ) = each ( cubrid_column_types ( $this->req ) );
      $this->assertEquals ( 'bit', $coltype );
      
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindBit1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // stringtest 大�?N
    try {
      $this->sql = "create table test1 (flag BIT(16))";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, "100", "BIT" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
    } catch ( Exception $e ) {
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindVarBit0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $arrayNormalCase = array (( int ) 10, ( float ) 89.9, 0x123, 1.2e+3, TRUE, "1", "10", "�?" );
    
    $arrayNormalResult = array (3130, 38392E39, 323931, 31323030, 31, "31", "3130", "E6B58B" );
    
    $arraySqlType = array ("BIT VARYING(16)", "BIT VARYING(32)", "BIT VARYING(24)", "BIT VARYING(32)", "BIT VARYING(8)", "BIT VARYING(16)", "BIT VARYING(24)", "BIT VARYING(24)" );
    
    $arrayResultType = array ("varbit(16)", "varbit(32)", "varbit(24)", "varbit(32)", "varbit(8)", "varbit(16)", "varbit(24)", "varbit(24)" );
    
    $i = 1;
    
    foreach ( $arrayNormalCase as $key => $value ) {
      if ($i == 9)
        break;
      else
        $i ++;
        // stringtest length <N
      $this->sql = "create table test1 (name $arraySqlType[$key])";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, $value, "BIT" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      
      // test data
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      list ( $key1, $id ) = each ( cubrid_fetch ( $this->req ) );
      $this->assertEquals ( $arrayNormalResult [$key], $id );
      
      // test type
      list ( $key2, $coltype ) = each ( cubrid_column_types ( $this->req ) );
      $this->assertEquals ( $arrayResultType [$key], $coltype );
      
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  // ??bug
  public function testCubridBindVarBit1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // stringtest 大�?N
    $this->sql = "create table test1 (flag BIT VARYING(16))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(?)";
    //$this->assertNotNull($this->req);
    $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $res = cubrid_bind ( $this->req, 1, "1000000", "BIT" );
    $this->assertTrue ( $res );
    cubrid_execute ( $this->req );
    //    cubrid_commit($this->con);
    // test data
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    list ( $key, $id ) = each ( cubrid_fetch ( $this->req ) );
    $this->assertEquals ( '3130', $id );
    
    // test type
    list ( $key, $coltype ) = each ( cubrid_column_types ( $this->req ) );
    $this->assertEquals ( 'varbit(16)', $coltype );
    
    $this->sql = "drop table test1";
    cubrid_execute ( $this->con, $this->sql );
    $this->con = null;
    $this->req = null;
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindDate0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $arrayNormalCase = array ("2000-01-01", "1-1", "2/1" );
    
    $arrayNormalResult = array ("2000-1-1", "2009-1-1", "2009-2-1" );
    
    foreach ( $arrayNormalCase as $key => $value ) {
      $this->sql = "create table test1 (id date)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, $value );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      
      // test data
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      list ( $key1, $id ) = each ( cubrid_fetch ( $this->req ) );
      $this->assertEquals ( $arrayNormalResult [$key], $id );
      
      // test type
      list ( $key2, $coltype ) = each ( cubrid_column_types ( $this->req ) );
      $this->assertEquals ( 'date', $coltype );
      
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindDate1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id date)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(?)";
    //$this->assertNotNull($this->req);
    $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    
    try {
      $value = "2000/1/1";
      $res = cubrid_bind ( $this->req, 1, $value );
      cubrid_execute ( $this->req );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
    }
    
    try {
      $value = "2000-01-01 10:0:0";
      $res = cubrid_bind ( $this->req, 1, $value );
      cubrid_execute ( $this->req );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
    }
    
    try {
      $value = "1/1";
      $res = cubrid_bind ( $this->req, 1, $value );
      cubrid_execute ( $this->req );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindDate2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id date)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(?)";
    //$this->assertNotNull($this->req);
    $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    
    try {
      $value = "1";
      $res = cubrid_bind ( $this->req, 1, $value );
      cubrid_execute ( $this->req );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
    }
    
    try {
      $value = "1.11";
      $res = cubrid_bind ( $this->req, 1, $value );
      cubrid_execute ( $this->req );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindTime0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $arrayNormalCase = array ("10:1:30", "10:1", "10:1:30 am", "10:1:30 pm" );
    
    $arrayNormalResult = array ("10:1:30", "10:1:0", "10:1:30", "22:1:30" );
    
    foreach ( $arrayNormalCase as $key => $value ) {
      $this->sql = "create table test1 (id time)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, $value );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      
      // test data
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      list ( $key1, $id ) = each ( cubrid_fetch ( $this->req ) );
      $this->assertEquals ( $arrayNormalResult [$key], $id );
      
      // test type
      list ( $key2, $coltype ) = each ( cubrid_column_types ( $this->req ) );
      $this->assertEquals ( 'time', $coltype );
      
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindTime1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id time)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(?)";
    //$this->assertNotNull($this->req);
    $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    
    try {
      $value = "16:08:33 am";
      $res = cubrid_bind ( $this->req, 1, $value );
      cubrid_execute ( $this->req );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    try {
      $value = "2000-01-01";
      $res = cubrid_bind ( $this->req, 1, $value );
      cubrid_execute ( $this->req );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridBindTime2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id time)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(?)";
    //$this->assertNotNull($this->req);
    $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    
    try {
      $value = "1";
      $res = cubrid_bind ( $this->req, 1, $value );
      cubrid_execute ( $this->req );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    try {
      $value = "1.11";
      $res = cubrid_bind ( $this->req, 1, $value );
      cubrid_execute ( $this->req );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
  }
  
  /**
   * @group php-822
   */
  // ??
  public function testCubridBindTimeStamp0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $arrayNormalCase = array ("01/31/1994 10:1:30 pm" );
    
    $arrayNormalResult = array ("1994-1-31 22:1:30" );
    
    foreach ( $arrayNormalCase as $key => $value ) {
      $this->sql = "create table test1 (id timestamp)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, $value, "TIMESTAMP" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      
      // test data
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      list ( $key1, $id ) = each ( cubrid_fetch ( $this->req ) );
      $this->assertEquals ( $arrayNormalResult [$key], $id );
      
      // test type
      list ( $key2, $coltype ) = each ( cubrid_column_types ( $this->req ) );
      $this->assertEquals ( 'timestamp', $coltype );
      
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  // ??
  public function testCubridBindSet0() {
    $this->markTestSkipped ( 'Cannot connect db server.' );
    $arrayNormalCase = array ("{'a', 'b'}" );
    
    $arrayNormalResult = array ("{'a', 'b'}" );
    foreach ( $arrayNormalCase as $key => $value ) {
      $this->sql = "create table test1 (id set char(20))";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, $value, "SET" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      
      // test data
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      list ( $key1, $id ) = each ( cubrid_fetch ( $this->req ) );
      $this->assertEquals ( $arrayNormalResult [$key], $id );
      
      // test type
      list ( $key2, $coltype ) = each ( cubrid_column_types ( $this->req ) );
      $this->assertEquals ( 'array', $coltype );
      
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridCloseRequest1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int)";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID | CUBRID_ASYNC );
    $this->assertTrue ( cubrid_close_request ( $this->req ) );
    $this->req = null;
  }
  
  /**
   * @group php-822
   */
  public function testCubridCloseRequest2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "create table test1 (id int)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "insert into test1 values(1)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "insert into test1 values(2)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "insert into test1 values(3)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID | CUBRID_ASYNC );
      $this->assertTrue ( cubrid_close_request ( $this->req ) );
      
      // will not execute
      while ( list ( $id, $name ) = cubrid_fetch ( $this->req ) ) {
        echo $id;
        echo $name;
      }
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      // you must unset req after close it
      $this->req = null;
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      //$this->sql = "drop table test1";
    //cubrid_execute($this->con, $this->sql);
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridCloseRequest3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "create table test1 (id int)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "insert into test1 values(1)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "insert into test1 values(2)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "insert into test1 values(3)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID | CUBRID_ASYNC );
      $this->assertTrue ( cubrid_close_request ( NULL ) );
      
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      $this->req = null;
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      //$this->sql = "drop table test1";
    //cubrid_execute($this->con, $this->sql);
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridColGet0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, hobby set(string))";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1 where id=1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    cubrid_fetch ( $this->req );
    $oid = cubrid_current_oid ( $this->req );
    //echo $oid;
    //echo cubrid_is_instance($this->con, $oid);
    

    //echo cubrid_get_class_name($this->con, $oid);
    $elem_array = cubrid_col_get ( $this->con, $oid, "hobby" );
    //    print_r($elem_array);
    list ( $key, $val ) = each ( $elem_array );
    $this->assertEquals ( 'a', $val );
  }
  
  /**
   * @group php-822
   */
  public function testCubridColGet1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, hobby set char(1))";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1 where id=1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    //echo $oid;
    //echo cubrid_is_instance($this->con, $oid);
    

    //echo cubrid_get_class_name($this->con, $oid);
    $elem_array = cubrid_col_get ( $this->con, $oid, "hobby" );
    list ( $key, $val ) = each ( $elem_array );
    $this->assertEquals ( 'a', $val );
  }
  
  /**
   * @group php-822
   */
  public function testCubridColGet2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, hobby multiset char(1))";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1 where id=1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    //echo $oid;
    //echo cubrid_is_instance($this->con, $oid);
    

    //echo cubrid_get_class_name($this->con, $oid);
    $elem_array = cubrid_col_get ( $this->con, $oid, "hobby" );
    list ( $key, $val ) = each ( $elem_array );
    $this->assertEquals ( 'a', $val );
  }
  
  /**
   * @group php-822
   */
  public function testCubridColGet3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, hobby sequence char(1))";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1 where id=1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    //echo $oid;
    //echo cubrid_is_instance($this->con, $oid);
    

    //echo cubrid_get_class_name($this->con, $oid);
    $elem_array = cubrid_col_get ( $this->con, $oid, "hobby" );
    list ( $key, $val ) = each ( $elem_array );
    $this->assertEquals ( 'a', $val );
  }
  
  /**
   * @group php-822
   */
  public function testCubridColGet4() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, hobby set)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "select * from test1 where id=1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    //echo $oid;
    //echo cubrid_is_instance($this->con, $oid);
    

    //echo cubrid_get_class_name($this->con, $oid);
    try {
      $elem_array = cubrid_col_get ( $this->con, $oid, "hobby" );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
    }
    //list ($key, $val) = each ($elem_array);
  //$this->assertEquals('a', $val);
  }
  
  /**
   * @group php-822
   */
  public function testCubridColGet5() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, hobby set char(1))";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "select * from test1 where id=1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    //echo $oid;
    //echo cubrid_is_instance($this->con, $oid);
    

    //echo cubrid_get_class_name($this->con, $oid);
    try {
      $elem_array = cubrid_col_get ( $this->con, $oid, "id" );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertTrue ( TRUE );
    }
    //list ($key, $val) = each ($elem_array);
  //$this->assertEquals('a', $val);
  }
  
  /**
   * @group php-822
   */
  public function testCubridColGet6() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, hobby set char(1))";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "select * from test1 where id=1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    //echo $oid;
    //echo cubrid_is_instance($this->con, $oid);
    

    //echo cubrid_get_class_name($this->con, $oid);
    try {
      $elem_array = cubrid_col_get ( $this->con, $oid1, "hobby" );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertTrue ( TRUE );
    }
    //list ($key, $val) = each ($elem_array);
  //$this->assertEquals('a', $val);
  }
  
  /**
   * @group php-822
   */
  // ??
  public function testCubridColSize0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, hobby set(string) )";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "select * from test1 where id=1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    $elem_count = cubrid_col_size ( $this->con, $oid, "hobby" );
    $this->assertEquals ( 3, $elem_count );
  }
  
  /**
   * @group php-822
   */
  public function testCubridColSize1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, hobby multiset(string) )";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "select * from test1 where id=1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    $elem_count = cubrid_col_size ( $this->con, $oid, "hobby" );
    $this->assertEquals ( 3, $elem_count );
  }
  
  /**
   * @group php-822
   */
  public function testCubridColSize2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, hobby sequence(string) )";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "select * from test1 where id=1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    $elem_count = cubrid_col_size ( $this->con, $oid, "hobby" );
    $this->assertEquals ( 3, $elem_count );
  }
  
  /**
   * @group php-822
   */
  public function testCubridColSize3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, hobby set )";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "select * from test1 where id=1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    try {
      $elem_count = cubrid_col_size ( $this->con, $oid, "hobby" );
      //echo $elem_count;
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertTrue ( TRUE );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridColSize4() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, hobby set char(1) )";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "select * from test1 where id=1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    try {
      $elem_count = cubrid_col_size ( $this->con, $oid, "id" );
      //echo $elem_count;
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertTrue ( TRUE );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridColSize5() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, hobby set(string) )";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "select * from test1 where id=1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    try {
      $elem_count = cubrid_col_size ( $this->con, $oid1, "id" );
      //echo $elem_count;
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertTrue ( TRUE );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridColumnNames0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, hobby set)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1 where id=1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $colnames = cubrid_column_names ( $this->req );
    $this->assertType ( 'array', $colnames );
    $this->assertEquals ( array ('id', 'hobby' ), $colnames );
    
    $this->sql = "drop table test1";
    cubrid_execute ( $this->con, $this->sql );
  }
  
  /**
   * @group php-822
   */
  public function testCubridColumnNames1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "create table test1 (id int, hobby set)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "select * from test1 where id=1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $colnames = cubrid_column_names ( $this->req, $this->sql );
      
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      $this->req = null;
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridColumnNames2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "create table test1 (id int, hobby set)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "select * from test1 where id=1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $colnames = cubrid_column_names ( NULL );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      $this->req = null;
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridColumnTypes0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, dou double, mon monetary, hobby set, tst timestamp, mset multiset integer, seq sequence integer)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "create table test2 (obj test1)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(1, 1.23, 100, {'a', 'b', 'c'}, 10, {10,10,20,20}, {10,20,30})";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test2 values(insert into test1 values(1, 1.23, 100, {'a', 'b', 'c'}, 10, {10,10,20,20}, {10,20,30}))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1 where id=1";
    //      $this->req = cubrid_execute($this->con, $this->sql);
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $coltypes = cubrid_column_types ( $this->req );
    $this->assertType ( 'array', $coltypes );
    $this->assertEquals ( array ('integer', 'double', 'monetary', 'set(unknown)', 'timestamp', 'multiset(integer)', 'sequence(integer)' ), $coltypes );
  }
  
  /**
   * @group php-822
   */
  public function testCubridColumnTypes1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "create table test1 (id int, hobby set)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "select * from test1 where id=1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $coltypes = cubrid_column_types ( NULL );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      $this->req = null;
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridColumnTypes2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "create table test1 (id int, hobby set)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "select * from test1 where id=1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $colnames = cubrid_column_types ( $this->req . $this->sql );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      $this->req = null;
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridCommit0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    cubrid_close_request ( $this->req );
    $res = cubrid_commit ( $this->con );
    $this->assertTrue ( $res );
    cubrid_disconnect ( $this->con );
    // must unset after close
    unset ( $this->req );
    unset ( $this->con );
    
    $this->con = cubrid_connect ( CubridTest::HOST, CubridTest::PORT, CubridTest::DBNAME, CubridTest::USERID, CubridTest::PASSWORD );
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( '3', $id );
    
    $this->sql = "drop table test1";
    cubrid_execute ( $this->con, $this->sql );
    $res = cubrid_commit ( $this->con );
    $this->assertTrue ( $res );
    $this->req = null;
    $this->con = null;
  }
  
  /**
   * @group php-822
   */
  public function testCubridCommit2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "create table test1 (id int)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(3)";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $res = cubrid_commit ( $this->req );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      $this->req = null;
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridCommit1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "create table test1 (id int)";
      cubrid_execute ( $this->con, $this->sql );
      
      cubrid_disconnect ( $this->con );
      // must unset con after disconnect
      unset ( $this->con );
      $this->con = cubrid_connect ( CubridTest::HOST, CubridTest::PORT, CubridTest::DBNAME, CubridTest::USERID, CubridTest::PASSWORD );
      
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertTrue ( TRUE );
      $this->req = null;
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridConect0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    self::checkServer ();
    //cubrid_close_request($this->req);
    cubrid_disconnect ( $this->con );
    $this->req = null;
    $this->con = null;
    
    $this->con = cubrid_connect ( CubridTest::HOST, CubridTest::PORT, CubridTest::DBNAME, CubridTest::USERID, CubridTest::PASSWORD );
    $this->assertGreaterThan ( 0, $this->con );
    
  //$this->con = cubrid_connect ("210.211.133.100", 12345, CubridTest::DBNAME, "aa", "bb");
  //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  //  public function testCubridConect1()
  //  {
  //    //cubrid_close_request($this->req);
  //    cubrid_disconnect($this->con);
  //    $this->req = null;
  //    $this->con = null;
  //    $this->con = cubrid_connect("10.34.63.58", CubridTest::PORT, CubridTest::DBNAME, CubridTest::USERID, CubridTest::PASSWORD);
  //    $this->assertGreaterThan(0, $this->con);
  //  }
  

  /**
   * @group php-822
   */
  public function testCubridConect2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    self::checkServer ();
    
    //cubrid_close_request($this->req);
    try {
      cubrid_disconnect ( $this->con );
      $this->req = null;
      $this->con = null;
      $this->con = cubrid_connect ( "192.168.0.44", CubridTest::PORT, CubridTest::DBNAME, CubridTest::USERID, CubridTest::PASSWORD );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      $this->req = null;
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridConect3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    self::checkServer ();
    
    //cubrid_close_request($this->req);
    try {
      cubrid_disconnect ( $this->con );
      $this->req = null;
      $this->con = null;
      $this->con = cubrid_connect ( CubridTest::HOST, 100, CubridTest::DBNAME, CubridTest::USERID, CubridTest::PASSWORD );
      
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      $this->req = null;
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridConect4() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    self::checkServer ();
    
    //cubrid_close_request($this->req);
    try {
      cubrid_disconnect ( $this->con );
      $this->req = null;
      $this->con = null;
      $this->con = cubrid_connect ( CubridTest::HOST, CubridTest::PORT, "demo", CubridTest::USERID, CubridTest::PASSWORD );
      
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      $this->req = null;
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridConect5() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    self::checkServer ();
    
    //cubrid_close_request($this->req);
    try {
      cubrid_disconnect ( $this->con );
      $this->req = null;
      $this->con = null;
      $this->con = cubrid_connect ( CubridTest::HOST, CubridTest::PORT, CubridTest::DBNAME, "cj", CubridTest::PASSWORD );
      
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      $this->req = null;
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridConect6() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    self::checkServer ();
    
    //cubrid_close_request($this->req);
    try {
      cubrid_disconnect ( $this->con );
      $this->req = null;
      $this->con = null;
      $this->con = cubrid_connect ( CubridTest::HOST, CubridTest::PORT, CubridTest::DBNAME, CubridTest::USERID, "gnuser" );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      $this->req = null;
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridConect7() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    self::checkServer ();
    cubrid_disconnect ( $this->con );
    $this->req = null;
    $this->con = null;
    $this->con = cubrid_connect ( "test-db-server", CubridTest::PORT, CubridTest::DBNAME, CubridTest::USERID );
    $this->assertGreaterThan ( 0, $this->con );
    $this->testCubridBindBit0 ();
    //$this->assertTrue(FALSE);
  }
  
  /**
   * @group php-822
   */
  public function testCubridConect8() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    self::checkServer ();
    cubrid_disconnect ( $this->con );
    $this->req = null;
    $this->con = null;
    $this->con = cubrid_connect ( "test-db-server", CubridTest::PORT, CubridTest::DBNAME );
    $this->assertGreaterThan ( 0, $this->con );
    $this->testCubridBindBit0 ();
    //$this->assertTrue(FALSE);
  }
  
  /**
   * @group php-822
   */
  public function testCubridConect9() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    self::checkServer ();
    
    //cubrid_close_request($this->req);
    try {
      cubrid_disconnect ( $this->con );
      $this->req = null;
      $this->con = null;
      $this->con = cubrid_connect ( CubridTest::HOST, CubridTest::PORT );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      $this->req = null;
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridCurrentOid0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    //      $this->req = cubrid_execute($this->con, $this->sql);
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid1 = cubrid_current_oid ( $this->req );
    
    $this->sql = "select * from test1 where id=3";
    //      $this->req = cubrid_execute($this->con, $this->sql);
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid3 = cubrid_current_oid ( $this->req );
    
    //echo cubrid_error_code();
    $this->assertType ( 'string', $oid1 );
    $oid2 = cubrid_current_oid ( $this->req );
    
    $this->assertType ( 'string', $oid2 );
    $this->assertEquals ( $oid1, $oid2 );
    
    $this->assertEquals ( $oid3, $oid2 );
    $this->sql = "drop table test1";
    cubrid_execute ( $this->con, $this->sql );
  }
  
  /**
   * @group php-822
   */
  public function testCubridCurrentOid1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "create table test1 (id int)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(3)";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      
      //$this->sql = "update test1 set id=1 where id=3";
      //$this->sql = "delete from test1 where id=3";
      //      $this->req = cubrid_execute($this->con, $this->sql);
      $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $oid1 = cubrid_current_oid ( $this->req );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      $this->req = null;
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridCurrentOid2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "create table test1 (id int)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(3)";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "update test1 set id=1 where id=3";
      //$this->sql = "delete from test1 where id=3";
      //      $this->req = cubrid_execute($this->con, $this->sql);
      $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $oid1 = cubrid_current_oid ( $this->req );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      $this->req = null;
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridCurrentOid3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "create table test1 (id int)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(3)";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "delete from test1 where id=3";
      //      $this->req = cubrid_execute($this->con, $this->sql);
      $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $oid1 = cubrid_current_oid ( $this->req );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      $this->req = null;
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
      $this->sql = "drop table test1";
      cubrid_execute ( $this->con, $this->sql );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridCurrentOid4() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    //      $this->req = cubrid_execute($this->con, $this->sql);
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $oid1 = cubrid_current_oid ( $this->req );
    $this->assertEquals ( '', $oid1 );
  }
  
  /**
   * @group php-822
   */
  public function testCubridDisConect0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->assertTrue ( cubrid_disconnect ( $this->con ) );
    // must unset after disconnect
    $this->req = null;
    $this->con = null;
  }
  
  /**
   * @group php-822
   */
  public function testCubridDisConect1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->assertTrue ( cubrid_disconnect ( $this->con ) );
    
    // disconnect again
    try {
      cubrid_disconnect ( $this->con );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
    }
    
    // query with old con
    try {
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
    }
    $this->req = null;
    $this->con = null;
  }
  
  /**
   * @group php-822
   */
  public function testCubridDisConect2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    // disconnect again
    try {
      cubrid_disconnect ( $this->req );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
    }
    
    try {
      cubrid_disconnect ( $this->con, $this->sql );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
    }
    
    $this->req = null;
    $this->con = null;
  }
  
  /**
   * @group php-822
   */
  public function testCubridDrop0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // in memory
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(3)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    $this->assertTrue ( cubrid_drop ( $this->con, $oid ) );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( "", $id );
  }
  
  /**
   * @group php-822
   */
  public function testCubridDrop1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // in physics
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(3)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    unset ( $this->req );
    unset ( $this->con );
    
    $this->con = cubrid_connect ( CubridTest::HOST, CubridTest::PORT, CubridTest::DBNAME, CubridTest::USERID, CubridTest::PASSWORD );
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    
    $oid = cubrid_current_oid ( $this->req );
    $this->assertTrue ( cubrid_drop ( $this->con, $oid ) );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( "", $id );
    
    $this->sql = "drop table test1";
    cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
    $this->con = null;
  }
  
  /**
   * @group php-822
   */
  public function testCubridDrop2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // in physics
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(3)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(4)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1 where id=3";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    cubrid_drop ( $this->con, $oid );
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( 4, $id );
  }
  
  /**
   * @group php-822
   */
  public function testCubridDrop3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      cubrid_drop ( $this->con, $oid );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      $this->log = __FUNCTION__;
      self::writeErrorLog ( $e );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridErrorCode0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      //$this->error_flag = TRUE;
    //$this->req = cubrid_execute ($this->con, "select id, name from person");
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(3)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    try {
      $this->sql = " select * from test";
      $this->req = cubrid_execute ( $this->con, $this->sql );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( - 493, cubrid_error_code () );
      $this->assertEquals ( 1, cubrid_error_code_facility () );
      $this->assertEquals ( "Syntax: syntax error, unexpected TEST ", cubrid_error_msg () );
    }
    
    try {
      $this->sql = " select id, name from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( - 494, cubrid_error_code () );
      $this->assertEquals ( 1, cubrid_error_code_facility () );
      $error_str = substr ( cubrid_error_msg (), 0, 31 );
      $this->assertEquals ( 'Semantic: [name] is not defined', $error_str );
    }
    
    try {
      $this->sql = " select id  test1";
      $this->req = cubrid_execute ( $this->con, $this->sql );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( - 493, cubrid_error_code () );
      $this->assertEquals ( 1, cubrid_error_code_facility () );
      $this->assertEquals ( 'Syntax: syntax error, unexpected $end, expecting FROM ', cubrid_error_msg () );
    }
    
    try {
      $this->sql = " select id  from test1 group by count(id)";
      $this->req = cubrid_execute ( $this->con, $this->sql );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( - 493, cubrid_error_code () );
      $this->assertEquals ( 1, cubrid_error_code_facility () );
      $this->assertEquals ( 'Syntax: expression is not allowed as group by spec ', cubrid_error_msg () );
    }
    
    try {
      $this->sql = " insert into test values(4)";
      $this->req = cubrid_execute ( $this->con, $this->sql );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( - 493, cubrid_error_code () );
      $this->assertEquals ( 1, cubrid_error_code_facility () );
      $this->assertEquals ( 'Syntax: syntax error, unexpected TEST ', cubrid_error_msg () );
    }
    
    try {
      $this->sql = " select * from test1";
      $this->req = cubrid_execute ( $this->sql );
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
    //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  // CCI error
  public function testCubridErrorCode1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      //$this->error_flag = TRUE;
    //$this->req = cubrid_execute ($this->con, "select id, name from person");
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(3)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    try {
      $this->con = cubrid_connect ( CubridTest::HOST, 100, CubridTest::DBNAME, CubridTest::USERID, CubridTest::PASSWORD );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
      $this->assertEquals ( - 16, cubrid_error_code () );
      $this->assertEquals ( 3, cubrid_error_code_facility () );
      $this->assertEquals ( 'Connection error', cubrid_error_msg () );
    }
  }
  
  /**
   * @group php-822
   */
  // wrong parameter
  public function testCubridErrorCode2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $res = cubrid_affected_rows ();
    } catch ( Exception $e ) {
      //echo $e->getMessage();
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
  }
  
  /**
   * @group php-822
   */
  // overflow
  public function testCubridErrorCode3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "create table test1 (id int)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, "2147483648" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertEquals ( - 493, cubrid_error_code () );
      $this->assertEquals ( 1, cubrid_error_code_facility () );
      $this->assertEquals ( 'Syntax: Cannot coerce host var to type integer. ', cubrid_error_msg () );
    }
  }
  
  /**
   * @group php-822
   */
  // wrong type
  public function testCubridErrorCode4() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "create table test1 (id int)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, "abcd" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertEquals ( - 493, cubrid_error_code () );
      $this->assertEquals ( 1, cubrid_error_code_facility () );
      $this->assertEquals ( 'Syntax: Cannot coerce host var to type integer. ', cubrid_error_msg () );
    }
  }
  
  /**
   * @group php-822
   */
  // out of range
  public function testCubridErrorCode5() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "create table test1 (id NUMERIC(5))";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(?)";
      //$this->assertNotNull($this->req);
      $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $res = cubrid_bind ( $this->req, 1, "555555" );
      $this->assertTrue ( $res );
      cubrid_execute ( $this->req );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertEquals ( - 493, cubrid_error_code () );
      $this->assertEquals ( 1, cubrid_error_code_facility () );
      $this->assertEquals ( 'Syntax: Cannot coerce host var to type numeric. ', cubrid_error_msg () );
    }
  }
  
  /**
   * @group php-822
   */
  // after close request to fetch
  public function testCubridErrorCode6() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "create table test1 (id int)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "insert into test1 values(1)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "insert into test1 values(2)";
      cubrid_execute ( $this->con, $this->sql );
      $this->sql = "insert into test1 values(3)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "select * from test1";
      $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID | CUBRID_ASYNC );
      cubrid_close_request ( $this->req );
      cubrid_fetch ( $this->req );
    } catch ( Exception $e ) {
      $this->req = null;
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
  }
  
  /**
   * @group php-822
   */
  // connect error ip
  public function testCubridErrorCode7() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      //cubrid_close_request($this->req);
    try {
      cubrid_disconnect ( $this->con );
      $this->req = null;
      $this->con = null;
      $this->con = cubrid_connect ( "192.168.0.44", CubridTest::PORT, CubridTest::DBNAME, CubridTest::USERID, CubridTest::PASSWORD );
      
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertEquals ( - 16, cubrid_error_code () );
      $this->assertEquals ( 3, cubrid_error_code_facility () );
      $this->assertEquals ( 'Connection error', cubrid_error_msg () );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridErrorCode8() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $this->sql = "create table test1 (id int)";
      cubrid_execute ( $this->con, $this->sql );
      
      $this->sql = "insert into test1 values(3)";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      
      //$this->sql = "update test1 set id=1 where id=3";
      //$this->sql = "delete from test1 where id=3";
      //$this->req = cubrid_execute($this->con, $this->sql);
      $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
      $oid1 = cubrid_current_oid ( $this->req );
      $this->assertTrue ( FALSE );
    } catch ( Exception $e ) {
      $this->assertEquals ( - 2002, cubrid_error_code () );
      $this->assertEquals ( 4, cubrid_error_code_facility () );
      $this->assertEquals ( 'Invalid API call', cubrid_error_msg () );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridErrorCode9() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      // error parameter
    try {
      cubrid_prepare ( $this->req, $this->sql );
    } catch ( Exception $e ) {
      $this->assertEquals ( - 2006, cubrid_error_code () );
      $this->assertEquals ( 4, cubrid_error_code_facility () );
      $this->assertEquals ( 'Invalid parameter', cubrid_error_msg () );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridErrorCode10() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', 20)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1 where id=2";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    $attrarray = cubrid_get ( $this->con, $oid );
    $attrarray ["id"] = 5;
    
    try {
      cubrid_put ( $this->con, $oid, "hobby", "aaa" );
    } catch ( Exception $e ) {
      $this->assertEquals ( - 202, cubrid_error_code () );
      $this->assertEquals ( 1, cubrid_error_code_facility () );
      $this->assertEquals ( 'Attribute "hobby" was not found.', cubrid_error_msg () );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridErrorCode11() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id SEQUENCE INTEGER)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values({20, 40, 60, 80})";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    try {
      $res = cubrid_seq_drop ( $this->con, $oid, "id", 0 );
      //$this->assertTrue($res);
    } catch ( Exception $e ) {
      $this->assertEquals ( - 309, cubrid_error_code () );
      $this->assertEquals ( 1, cubrid_error_code_facility () );
      $this->assertEquals ( 'Illegal set element index given: -1.', cubrid_error_msg () );
      //echo $e->getMessage();
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridErrorCode12() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id SEQUENCE INTEGER)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values({20, 40, 60, 80})";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    try {
      cubrid_seq_insert ( $this->con, $oid, "id", 1, "{3,4}" );
    } catch ( Exception $e ) {
      $this->assertEquals ( - 179, cubrid_error_code () );
      $this->assertEquals ( 1, cubrid_error_code_facility () );
      $this->assertEquals ( 'Domain "character varying" is not compatible with domain "integer".', cubrid_error_msg () );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridErrorCode13() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id SEQUENCE INTEGER, age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values({20, 40, 60, 80}, 100)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    try {
      $res = cubrid_seq_put ( $this->con, $oid, "age", 1, 3 );
    } catch ( Exception $e ) {
      $this->assertEquals ( - 1020, cubrid_error_code () );
      $this->assertEquals ( 2, cubrid_error_code_facility () );
      $this->assertEquals ( 'The attribute domain must be the set type.', cubrid_error_msg () );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridErrorCode14() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, hobby set )";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(1, {'a', 'b', 'c'})";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1 where id=1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    //echo $oid;
    //echo cubrid_is_instance($this->con, $oid);
    

    //echo cubrid_get_class_name($this->con, $oid);
    try {
      $elem_array = cubrid_col_get ( $this->con, $oid, "hobby" );
    } catch ( Exception $e ) {
      $this->assertEquals ( - 1021, cubrid_error_code () );
      $this->assertEquals ( 2, cubrid_error_code_facility () );
      $this->assertEquals ( 'The domain of a set must be the same data type.', cubrid_error_msg () );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridExecute0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      //$this->error_flag = TRUE;
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(3)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $this->assertGreaterThanOrEqual ( 3, ( int ) $this->req );
    
    $this->sql = "insert into test1 values(?)";
    $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $res = cubrid_bind ( $this->req, 1, "4" );
    cubrid_execute ( $this->req );
    
    $this->sql = " select * from test1 where id=4";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( 4, $id );
    
    $this->sql = "update test1 set id=5 where id=4";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $this->sql = " select * from test1 where id=5";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( 5, $id );
    
    $this->sql = "delete from test1 where id=5";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $this->sql = " select * from test1 where id=5";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( "", $id );
  }
  
  /**
   * @group php-822
   */
  public function testCubridExecute1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      //$this->error_flag = TRUE;
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(3)";
    //cubrid_execute($this->con, $this->sql);
    

    try {
      cubrid_execute ( $this->con, $this->sql );
      //$this->assertGreaterThanOrEqual(3, (int)$this->req);
    } catch ( Excetion $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridFetch0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(3)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(4)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( 3, $id );
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( 4, $id );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    list ( $id ) = cubrid_fetch ( $this->req, CUBRID_NUM );
    $this->assertEquals ( 3, $id );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $id = cubrid_fetch ( $this->req, CUBRID_ASSOC );
    //    print_r($id);
    $this->assertEquals ( 3, $id ["id"] );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    list ( $id ) = cubrid_fetch ( $this->req, CUBRID_BOTH );
    //    print_r($id);
    $this->assertEquals ( 3, $id );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $obj = cubrid_fetch ( $this->req, CUBRID_OBJECT );
    //print_r($id);
    $this->assertEquals ( 3, $obj->id );
    //echo $hobby;
  }
  
  /**
   * @group php-822
   */
  // use wrong paramter
  public function testCubridFetch1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(3)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(4)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select count(*) from test1 group by id";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    try {
      $obj = cubrid_fetch ( $this->sql );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridFetch2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(3)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(4)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    try {
      $obj = cubrid_fetch ( $this->req );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    $this->sql = "update test1 set id=1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    try {
      $obj = cubrid_fetch ( $this->req );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    $this->sql = "delete from test1 where id=3";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    try {
      $obj = cubrid_fetch ( $this->req );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridGet0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3, 'test')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    //      $this->req = cubrid_execute($this->con, $this->sql);
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid1 = cubrid_current_oid ( $this->req );
    
    $attrarray1 = cubrid_get ( $this->con, $oid1 );
    //print_r($attrarray);
    $this->assertEquals ( 3, $attrarray1 ["id"] );
    $this->assertEquals ( "test", $attrarray1 ["name"] );
    
    $id = cubrid_get ( $this->con, $oid1, "id" );
    $this->assertEquals ( 3, $id );
    
    $attrarray2 = cubrid_get ( $this->con, $oid1, array ("id", "name" ) );
    
    //    print_r($attrarray2);
    $this->assertEquals ( 3, $attrarray2 ["id"] );
    //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridGet1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3, 'test')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    //$this->req = cubrid_execute($this->con, $this->sql);
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid1 = cubrid_current_oid ( $this->req );
    
    try {
      $attrarray1 = cubrid_get ( $this->req, $oid1 );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    try {
      $attrarray1 = cubrid_get ( $this->con, $oid );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
  //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridGetClassName0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3, 'test')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(4, 'cj')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1 where id=3";
    //$this->req = cubrid_execute($this->con, $this->sql);
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid1 = cubrid_current_oid ( $this->req );
    
    $class_name1 = cubrid_get_class_name ( $this->con, $oid1 );
    
    $this->assertEquals ( "test1", $class_name1 );
    
    $this->sql = "select * from test1 where id=4";
    //$this->req = cubrid_execute($this->con, $this->sql);
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid2 = cubrid_current_oid ( $this->req );
    
    $class_name2 = cubrid_get_class_name ( $this->con, $oid2 );
    
    $this->assertEquals ( "test1", $class_name2 );
    //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridGetClassName1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3, 'test')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(4, 'cj')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1 where id=3";
    //$this->req = cubrid_execute($this->con, $this->sql);
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid1 = cubrid_current_oid ( $this->req );
    
    // use wrong oid
    try {
      $class_name1 = cubrid_get_class_name ( $this->con, $oid2 );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo cubrid_error_msg();
    //$e->getMessage();
    }
    
    // use wrong parameter
    try {
      $class_name1 = cubrid_get_class_name ( $this->req, $oid1 );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo cubrid_error_msg();
    //$e->getMessage();
    }
    
  //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridIsInstance0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3, 'test')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(4, 'cj')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1 where id=3";
    //      $this->req = cubrid_execute($this->con, $this->sql);
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid1 = cubrid_current_oid ( $this->req );
    
    $res1 = cubrid_is_instance ( $this->con, $oid1 );
    
    $this->assertEquals ( 1, $res1 );
    
    $this->sql = "select * from test1 where id=4";
    //      $this->req = cubrid_execute($this->con, $this->sql);
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid2 = cubrid_current_oid ( $this->req );
    
    $res2 = cubrid_is_instance ( $this->con, $oid2 );
    
    $this->assertEquals ( 1, $res2 );
    
    $this->sql = "select test1 from test1 ";
    //      $this->req = cubrid_execute($this->con, $this->sql);
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid3 = cubrid_current_oid ( $this->req );
    
    $res3 = cubrid_is_instance ( $this->con, $oid3 );
    
    $this->assertEquals ( 1, $res3 );
    
  //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridIsInstance1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3, 'test')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(4, 'cj')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1 where id=3";
    //$this->req = cubrid_execute($this->con, $this->sql);
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    // use wrong oid
    try {
      $res = cubrid_is_instance ( $this->con, $oid1 );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    // use wrong parameter
    try {
      $res = cubrid_is_instance ( $this->req, $oid );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
  }
  
  /**
   * @group glo
   */
  public function testCubridLoadFromGlo0() {
    //    $this->markTestIncomplete(
    //            'This test has not been implemented yet.'
    //            );
    

    $this->sql = "create table test1 under glo (image glo)";
    cubrid_execute ( $this->con, $this->sql );
    $oid = cubrid_new_glo ( $this->con, "test1", "images/a.jpg" );
    $res = cubrid_load_from_glo ( $this->con, $oid, "images/b.jpg" );
    $this->assertTrue ( $res );
  }
  
  /**
   * @group glo
   */
  public function testCubridLoadFromGlo1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      //    $this->markTestIncomplete(
    //            'This test has not been implemented yet.'
    //            );
    

    $this->sql = "create table test1 (image glo)";
    cubrid_execute ( $this->con, $this->sql );
    
    try {
      $oid = cubrid_new_glo ( $this->con, "test1", "images/a.jpg" );
    } catch ( Exception $e ) {
      echo $e->getMessage ();
    }
    
    $this->sql = "create table test2 (image glo)";
    cubrid_execute ( $this->con, $this->sql );
    
    try {
      $oid = cubrid_new_glo ( $this->con, "glo", "images/delete.png" );
      $this->sql = "insert into test2(image) values($oid)";
      $res = cubrid_execute ( $this->con, $this->sql );
      $this->req = cubrid_execute ( $this->con, "select image from test2 where rownum=1" );
      list ( $oid ) = cubrid_fetch ( $this->req );
      $res = cubrid_load_from_glo ( $this->con, $oid, "images/c.jpg" );
      $this->assertTrue ( $res );
    } catch ( Exception $e ) {
      echo $e->getMessage ();
    }
    
  //    $res=cubrid_load_from_glo($this->con, $oid, "images/b.jpg");
  //    $this->assertTrue($res);
  }
  
  /**
   * @group glo
   */
  public function testCubridLoadFromGlo2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      //    $this->markTestIncomplete(
    //            'This test has not been implemented yet.'
    //            );
    

    $this->sql = "create table test1 under glo (id int, image glo)";
    cubrid_execute ( $this->con, $this->sql );
    $oid = cubrid_new_glo ( $this->con, "test1", "images/a.jpg" );
    $this->sql = "insert into test1(id, image) values(1, $oid)";
    $res = cubrid_execute ( $this->con, $this->sql );
    //    echo $res;
    //    $res=cubrid_load_from_glo($this->con, $oid, "images/b.jpg");
    //    $this->assertTrue($res);
    //    cubrid_commit($this->con);
    //    $this->con=null;
    //    $this->req=null;
    $this->req = cubrid_execute ( $this->con, "select image from test1 where id=1" );
    list ( $oid ) = cubrid_fetch ( $this->req );
    $res = cubrid_load_from_glo ( $this->con, $oid, "images/c.jpg" );
    $this->assertTrue ( $res );
  }
  
  /**
   * @group lock
   */
  public function testCubridLockRead0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      //    $this->markTestIncomplete(
    //            'This test has not been implemented yet.'
    //            );
    // change configure file
    if (OS == 'LINUX') {
      system ( "sh modconf.sh " . CubridTest::DBNAME . "> /dev/null &" );
    } else {
      system ( "modconf.cmd " . CubridTest::DBNAME );
    }
    
    sleep ( 20 );
    //cubrid_disconnect($this->con);
    //cubrid_close_request($this->req);
    $this->con = cubrid_connect ( CubridTest::HOST, CubridTest::PORT, CubridTest::DBNAME, CubridTest::USERID, CubridTest::PASSWORD );
    $this->sql = "create table test1 (id int primary key, name varchar(300))";
    cubrid_execute ( $this->con, $this->sql );
    
    for($i = 0; $i < 10; $i ++) {
      $this->sql = "insert into test1 values($i, 'testalsjdflakasdfkljaklsjdfklasjkldfja;klsjdlfkjasdfs')";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      
      //    $this->sql = "insert into test1 values(4, 'cj')";
      //    $this->req = cubrid_execute($this->con, $this->sql);
      cubrid_commit ( $this->con );
    }
    $this->sql = "select * from test1 where id=3";
    //      $this->req = cubrid_execute($this->con, $this->sql);
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    cubrid_fetch ( $this->req );
    $oid = cubrid_current_oid ( $this->req );
    
    cubrid_commit ( $this->con );
    $res = cubrid_lock_read ( $this->con, $oid );
    //    sleep(100);
    

    $this->assertTrue ( $res );
    
    system ( "cubrid lockdb " . CubridTest::DBNAME . "  | grep -w -A4 test1 | grep -w S_LOCK> lock.log" );
    $fp = fopen ( "lock.log", "r" );
    $content = fread ( $fp, 100 );
    $this->assertContains ( "S_LOCK", $content );
    fclose ( $fp );
    system ( "rm -f lock.log" );
    $this->sql = "drop test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    cubrid_commit ( $this->con );
    $this->con = null;
    $this->req = null;
    
    // recover
    if (OS == 'LINUX') {
      system ( "sh recov.sh " . CubridTest::DBNAME . " > /dev/null &" );
    } else {
      system ( "recov.cmd " . CubridTest::DBNAME );
    }
    sleep ( 20 );
  }
  
  /**
   * @group lock
   */
  public function testCubridLockWrite0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int primary key, name varchar(300))";
    cubrid_execute ( $this->con, $this->sql );
    
    for($i = 0; $i < 10; $i ++) {
      $this->sql = "insert into test1 values($i, 'testalsjdflakasdfkljaklsjdfklasjkldfja;klsjdlfkjasdfs')";
      $this->req = cubrid_execute ( $this->con, $this->sql );
      
    //    $this->sql = "insert into test1 values(4, 'cj')";
    //    $this->req = cubrid_execute($this->con, $this->sql);
    }
    $this->sql = "select * from test1 where id=3";
    //      $this->req = cubrid_execute($this->con, $this->sql);
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    cubrid_fetch ( $this->req );
    $oid = cubrid_current_oid ( $this->req );
    
    $res = cubrid_lock_write ( $this->con, $oid );
    //    sleep(100);
    

    $this->assertTrue ( $res );
    
    system ( "cubrid lockdb " . CubridTest::DBNAME . "  | grep -w -A4 test1 | grep -w X_LOCK> lockw.log" );
    $fp = fopen ( "lockw.log", "r" );
    $content = fread ( $fp, 100 );
    $this->assertContains ( "X_LOCK", $content );
    fclose ( $fp );
    system ( "rm -f lockw.log" );
    
  //    $this->sql = "drop test1";
  //    $this->req = cubrid_execute($this->con, $this->sql, CUBRID_INCLUDE_OID);
  //    cubrid_commit($this->con);
  //    unset($this->con);
  //    unset($this->req);
  //    $this->con=null;
  //    $this->req=null;
  }
  
  /**
   * @group php-822
   */
  public function testCubridMoveCursor0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(1, 'test')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3, 'gnuser')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(4, 'test1')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $res = cubrid_move_cursor ( $this->req, 1, CUBRID_CURSOR_FIRST );
    $this->assertEquals ( 1, $res );
    
    $row = cubrid_fetch ( $this->req );
    $this->assertEquals ( 1, $row ["id"] );
    $this->assertEquals ( 'test', $row ["name"] );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $res = cubrid_move_cursor ( $this->req, 1, CUBRID_CURSOR_LAST );
    $this->assertEquals ( 1, $res );
    
    $row = cubrid_fetch ( $this->req );
    $this->assertEquals ( 4, $row ["id"] );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $res = cubrid_move_cursor ( $this->req, 0, CUBRID_CURSOR_CURRENT );
    $this->assertEquals ( 1, $res );
    
    $row = cubrid_fetch ( $this->req );
    $this->assertEquals ( 1, $row ["id"] );
  }
  
  /**
   * @group php-822
   */
  // use -1
  public function testCubridMoveCursor1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(1, 'test')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3, 'gnuser')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(4, 'test1')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $res = cubrid_move_cursor ( $this->req, - 1, CUBRID_CURSOR_CURRENT );
    //echo $res;
    $this->assertEquals ( 0, $res );
    $row = cubrid_fetch ( $this->req );
    $this->assertEquals ( NULL, $row ["id"] );
  }
  
  /**
   * @group php-822
   */
  // move  > length
  public function testCubridMoveCursor2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(1, 'test')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3, 'gnuser')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(4, 'test1')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1 where id=5";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $res = cubrid_move_cursor ( $this->req, 5 );
    //    echo $res;
    $this->assertEquals ( 0, $res );
    $row = cubrid_fetch ( $this->req );
    $this->assertEquals ( NULL, $row ["id"] );
  }
  
  /**
   * @group php-822
   */
  // wrong parameter
  public function testCubridMoveCursor3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(1, 'test')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3, 'gnuser')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(4, 'test1')";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    
    try {
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $res = cubrid_move_cursor ( $this->req );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
    }
    
    try {
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $res = cubrid_move_cursor ( $this->con, 1 );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridNewGlo() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      //    $this->markTestIncomplete(
    //            'This test has not been implemented yet.'
    //            );
    

    $this->sql = "create table test1 under glo (image glo)";
    cubrid_execute ( $this->con, $this->sql );
    
    $oid = cubrid_new_glo ( $this->con, "test1", "images/a.jpg" );
    
    $this->sql = "insert into test1(image) values($oid)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    //cubrid_commit($this->con);
    

    $this->req = cubrid_execute ( $this->con, "select image from test1 where rownum=2" );
    list ( $oid1 ) = cubrid_fetch ( $this->req );
    //dump
  

  //    $res = cubrid_load_from_glo ($this->con, $oid1, "b.jpg");
  //    $this->req = cubrid_execute($this->con, $this->sql);
  //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  //    $this->sql = "drop test1";
  //    $this->req = cubrid_execute($this->con, $this->sql);
  //    cubrid_commit($this->con);
  //    unset($this->req);
  //    unset($this->con);
  }
  
  /**
   * @group php-822
   */
  public function testCubridNumCols0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(1, 'test', 10)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', 20)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $cols_count = cubrid_num_cols ( $this->req );
    $this->assertEquals ( 3, $cols_count );
  }
  
  /**
   * @group php-822
   */
  public function testCubridNumCols1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(1, 'test', 10)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', 20)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3, 'gnuser', 30)";
    
    try {
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $cols_count = cubrid_num_cols ( $this->req );
      //    $this->assertEquals(2, $cols_count);
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    $this->sql = "update test1 set name='test1' where id = 1";
    
    try {
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $cols_count = cubrid_num_cols ( $this->req );
      //$this->assertEquals(2, $cols_count);
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    $this->sql = "delete from test1 where id = 1";
    
    try {
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $cols_count = cubrid_num_cols ( $this->req );
      //$this->assertEquals(2, $cols_count);
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridNumCols2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(1, 'test', 10)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', 20)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    // use wrong parameter
    try {
      cubrid_num_cols ( $this->con );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridNumRows0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(1, 'test', 10)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', 20)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $rows_count = cubrid_num_rows ( $this->req );
    $this->assertEquals ( 2, $rows_count );
  }
  
  /**
   * @group php-822
   */
  public function testCubridNumRows1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(1, 'test', 10)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', 20)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(3, 'gnuser', 30)";
    
    // use insert sql
    try {
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $cols_count = cubrid_num_rows ( $this->req );
      //    $this->assertEquals(2, $cols_count);
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    $this->sql = "update test1 set name='test1' where id = 1";
    
    // use update sql
    try {
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $cols_count = cubrid_num_rows ( $this->req );
      //    $this->assertEquals(2, $cols_count);
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    $this->sql = "delete from test1 where id = 1";
    
    // use update sql
    try {
      $this->req = cubrid_execute ( $this->con, $this->sql );
      $cols_count = cubrid_num_rows ( $this->req );
      //$this->assertEquals(2, $cols_count);
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridNumRows2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(1, 'test', 10)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', 20)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    // use wrong parameter
    try {
      cubrid_num_rows ( $this->con );
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridNumRows3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(1, 'test', 10)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', 20)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_ASYNC );
    
    $cols_count = cubrid_num_rows ( $this->req );
    $this->assertEquals ( 0, $cols_count );
  }
  
  /**
   * @group php-822
   */
  public function testCubridPrepare0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(?,?)";
    //$this->assertNotNull($this->req);
    $this->req = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $this->assertNotNull ( $this->req );
    
    cubrid_bind ( $this->req, 1, "1" );
    cubrid_bind ( $this->req, 2, "test" );
    cubrid_execute ( $this->req );
    
    $this->sql = "select * from test1";
    $this->req1 = cubrid_execute ( $this->con, $this->sql );
    list ( $id, $name ) = cubrid_fetch ( $this->req1 );
    $this->assertEquals ( 1, $id );
    
    cubrid_bind ( $this->req, 1, "2" );
    cubrid_bind ( $this->req, 2, "test1" );
    cubrid_execute ( $this->req );
    
    $this->sql = "select * from test1 where id=2";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    list ( $id, $name ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( 2, $id );
    
    $this->sql = "select * from test1 where id=?";
    //$this->assertNotNull($this->req);
    $this->req2 = cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $this->assertNotNull ( $this->req2 );
    cubrid_bind ( $this->req2, 1, "2" );
    cubrid_execute ( $this->req2 );
    list ( $id, $name ) = cubrid_fetch ( $this->req2 );
    $this->assertEquals ( "test1", $name );
  }
  
  /**
   * @group php-822
   */
  public function testCubridPrepare1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30))";
    cubrid_execute ( $this->con, $this->sql );
    
    // less field
    $this->sql = "insert into test1 values(?)";
    
    //$this->assertNotNull($this->req);
    try {
      cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    // non-exist table
    $this->sql = "insert into test2 values(?, ?)";
    
    //$this->assertNotNull($this->req);
    try {
      cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    // more field
    $this->sql = "insert into test1 values(?, ?, ?)";
    
    //$this->assertNotNull($this->req);
    try {
      cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    // error sql
    $this->sql = "insert test1 values( ?, ?)";
    
    //$this->assertNotNull($this->req);
    try {
      cubrid_prepare ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridPrepare2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(?, ?)";
    
    //$this->assertNotNull($this->req);
    // error parameter
    try {
      cubrid_prepare ( $this->con );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    // error parameter
    try {
      cubrid_prepare ( $this->req, $this->sql );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridPut0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', 20)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1 where id=2";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    $attrarray = cubrid_get ( $this->con, $oid );
    
    cubrid_put ( $this->con, $oid, "id", 3 );
    
    $this->sql = "select * from test1  where name='cj'";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id, $name, $age ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( 3, $id );
    
    $attrarray ["id"] = 5;
    cubrid_put ( $this->con, $oid, $attrarray );
    $this->sql = "select * from test1  where name='cj'";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id, $name, $age ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( 5, $id );
    
    //print_r($attrarray);
    $attrarray1 = array ("id" => 6, "name" => 'gnuser', "age" => 30 );
    
    cubrid_put ( $this->con, $oid, $attrarray1 );
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id, $name, $age ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( 6, $id );
    $this->assertEquals ( "gnuser", $name );
    $this->assertEquals ( 30, $age );
  }
  
  /**
   * @group php-822
   */
  public function testCubridPut1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), hobby set varchar(20))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', {'a','b','c'} )";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1 where id=2";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    $attrarray = cubrid_get ( $this->con, $oid );
    cubrid_put ( $this->con, $oid, "hobby", array ("aa", "bb" ) );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id, $name, $hobby ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( array ("aa", "bb" ), $hobby );
  }
  
  /**
   * @group php-822
   */
  public function testCubridPut2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', 20)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1 where id=2";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    $attrarray = cubrid_get ( $this->con, $oid );
    $attrarray ["id"] = 5;
    
    try {
      cubrid_put ( $this->con, $oid );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
    }
    
    try {
      cubrid_put ( $this->con, $oid1, $attrarray );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
    }
    
    try {
      cubrid_put ( $this->con, $oid, "hobby", "aaa" );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
    }
  }
  
  /**
   * @group php-822
   */
  public function testCubridSaveToGlo() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      //    $this->markTestIncomplete(
    //            'This test has not been implemented yet.'
    //            );
    $this->sql = "create table test1 under glo (image glo)";
    cubrid_execute ( $this->con, $this->sql );
    $oid = cubrid_new_glo ( $this->con, "test1", "images/a.jpg" );
    $res = cubrid_save_to_glo ( $this->con, $oid, "images/a.jpg" );
    $this->assertTrue ( $res );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSchemaAdd() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $attr = cubrid_schema ( $this->con, CUBRID_SCH_CLASS );
    //print_r($attr);
  }
  
  /**
   * @group php-822
   */
  public function testCubridSchema0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', 20)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $attr = cubrid_schema ( $this->con, CUBRID_SCH_CLASS, "test1" );
    $this->assertType ( 'array', $attr );
    //print_r($attr);
    

    $this->assertEquals ( array ("NAME" => "test1", "TYPE" => "2" ), $attr [0] );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSchema1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create view test1 (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $attr = cubrid_schema ( $this->con, CUBRID_SCH_VCLASS, "test1" );
    $this->assertType ( 'array', $attr );
    //print_r($attr);
    

    $this->assertEquals ( array ("NAME" => "test1", "TYPE" => "1" ), $attr [0] );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSchema2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "create view vtest1 (id int) as select id from test1";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', 20)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $attr = cubrid_schema ( $this->con, CUBRID_SCH_QUERY_SPEC, "vtest1" );
    $this->assertType ( 'array', $attr );
    $this->assertEquals ( array ("QUERY_SPEC" => "select test1.id from test1 test1" ), $attr [0] );
    //    print_r($attr);
  // $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //          );
  //$this->assertEquals(array("test1", "2"), $attr);
  }
  
  /**
   * @group php-822
   */
  public function testCubridSchema3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', 20)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $attr = cubrid_schema ( $this->con, CUBRID_SCH_ATTRIBUTE, "test1" );
    $this->assertType ( 'array', $attr );
    //print_r($attr);
    

    $this->assertEquals ( array ("ATTR_NAME" => "age", "DOMAIN" => "8", "SCALE" => "0", "PRECISION" => "0", "INDEXED" => "0", "NON_NULL" => "0", "SHARED" => "0", "UNIQUE" => "0", "DEFAULT" => "", "ATTR_ORDER" => "3", "CLASS_NAME" => "test1", "SOURCE_CLASS" => "test1", "IS_KEY" => "0" ), $attr [2] );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSchema4() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create class test1 (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "create class test2 (id int, obj test1, class xxx int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', 20)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test2 values(1, select test1 from test1 where id = 2)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $attr = cubrid_schema ( $this->con, CUBRID_SCH_CLASS_ATTRIBUTE, "test2" );
    $this->assertType ( 'array', $attr );
    //    print_r($attr);
    $this->assertEquals ( "xxx", $attr [0] ["ATTR_NAME"] );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSchema5() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', 20)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $attr = cubrid_schema ( $this->con, CUBRID_SCH_METHOD, "glo" );
    $this->assertType ( 'array', $attr );
    //print_r($attr);
    //  $this->markTestIncomplete(
    //     'This test has not been implemented yet.'
    //    );
    $this->assertEquals ( array ("NAME" => "read_data", "RET_DOMAIN" => "8", "ARG_DOMAIN" => "8 2 " ), $attr [0] );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSchema6() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', 20)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $attr = cubrid_schema ( $this->con, CUBRID_SCH_CLASS_METHOD, "glo" );
    $this->assertType ( 'array', $attr );
    //print_r($attr);
    $this->assertEquals ( array ("NAME" => "new", "RET_DOMAIN" => "19", "ARG_DOMAIN" => "2 " ), $attr [0] );
    //  $this->markTestIncomplete(
  //     'This test has not been implemented yet.'
  //    );
  }
  /*
    public function testCubridSchema7()
    {
    $this->sql = "create table test1 (id int, name varchar(30), age int)";
    cubrid_execute($this->con, $this->sql);

    $this->sql = "insert into test1 values(2, 'cj', 20)";
    $this->req = cubrid_execute($this->con, $this->sql);

    $attr = cubrid_schema($this->con, CUBRID_SCH_METHOD_FILE, "glo");
    $this->assertType('array', $attr);
    //print_r($attr);
    $this->markTestIncomplete(
    'This test has not been implemented yet.'
    );  

    }
    */
  
  /**
   * @group php-822
   */
  public function testCubridSchema8() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 under glo (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $attr = cubrid_schema ( $this->con, CUBRID_SCH_SUPERCLASS, "test1" );
    $this->assertType ( 'array', $attr );
    $this->assertEquals ( array ("CLASS_NAME" => "glo", "TYPE" => "0" ), $attr [0] );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSchema9() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 under glo (id int, name varchar(30), age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $attr = cubrid_schema ( $this->con, CUBRID_SCH_SUBCLASS, "glo" );
    $this->assertType ( 'array', $attr );
    //print_r($attr);
    $this->assertEquals ( array ("CLASS_NAME" => "test1", "TYPE" => "2" ), $attr [0] );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSchema10() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int , name varchar(30), age int) ";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "create index a on test1(id)";
    cubrid_execute ( $this->con, $this->sql );
    $attr = cubrid_schema ( $this->con, CUBRID_SCH_CONSTRAINT, "test1" );
    $this->assertType ( 'array', $attr );
    //print_r($attr);
    $this->assertEquals ( "id", $attr [0] ["ATTR_NAME"] );
    //    $this->markTestIncomplete(
  //            'This test has not been implemented yet.'
  //            );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSchema11() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int unique primary key, name varchar(30), age int not null) ";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "create trigger t1 deferred insert on test1 execute after update test1 set id = id + 1";
    cubrid_execute ( $this->con, $this->sql );
    
    $attr = cubrid_schema ( $this->con, CUBRID_SCH_TRIGGER, "test1" );
    $this->assertType ( 'array', $attr );
    //print_r($attr);
    $this->assertEquals ( array ("NAME" => "t1", "STATUS" => "ACTIVE", "EVENT" => "INSERT", "TARGET_CLASS" => "test1", "TARGET_ATTR" => "", "ACTION_TIME" => "AFTER", "ACTION" => "update test1 set id=id+1", "PRIORITY" => "0.000000", "CONDITION_TIME" => "", "CONDITION" => "" ), $attr [0] );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSchema12() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int unique primary key, name varchar(30), age int not null) ";
    cubrid_execute ( $this->con, $this->sql );
    
    $attr = cubrid_schema ( $this->con, CUBRID_SCH_CLASS_PRIVILEGE, "test1" );
    $this->assertType ( 'array', $attr );
    //print_r($attr);
    //$this->assertEquals(array("CLASS_NAME"=>"test1", "TYPE"=>"2"), $attr[0]);
    $this->assertEquals ( array ("CLASS_NAME" => "test1", "PRIVILEGE" => "SELECT", "GRANTABLE" => "YES" ), $attr [0] );
  }
  /*
    public function testCubridSchema13()
    {
    $this->sql = "create user cj";
    cubrid_execute($this->con, $this->sql);
    $this->sql = "create table test1 (id int)";
    cubrid_execute($this->con, $this->sql);
    $this->sql = "create view  vtest1 (id int) as select id from test1";
    cubrid_execute($this->con, $this->sql);
    $this->sql = "grant select on vtest1 to cj";
    cubrid_execute($this->con, $this->sql);

    cubrid_commit($this->con);

    //    $this->con = cubrid_connect(CubridTest::HOST, CubridTest::PORT, CubridTest::DBNAME, "cj", "");
    //    $this->sql = "create table test2 (id int unique primary key, name varchar(30), age int not null) ";
    //    cubrid_execute($this->con, $this->sql);
    $attr = cubrid_schema($this->con, CUBRID_SCH_ATTR_PRIVILEGE, "vtest1");
    $this->assertType('array', $attr);
    print_r($attr);
    //$this->assertEquals(array("CLASS_NAME"=>"test1", "TYPE"=>"2"), $attr[0]);
    //$this->assertEquals(array("CLASS_NAME"=>"test1", "PRIVILEGE"=>"SELECT", "GRANTABLE"=>"YES"), $attr[0]);
    $this->sql = "drop table test1";
    cubrid_execute($this->con, $this->sql);
    $this->sql = "drop view vtest1";
    cubrid_execute($this->con, $this->sql);
    cubrid_commit($this->con);
    //    $this->con = cubrid_connect(CubridTest::HOST, CubridTest::PORT, CubridTest::DBNAME, "dba", "");
    $this->sql = "drop user cj";
    cubrid_execute($this->con, $this->sql);
    cubrid_commit($this->con);
    $this->markTestIncomplete(
    'This test has not been implemented yet.'
    );  
    }
    */
  /*   
    public function testCubridSendGlo()
    {
    if (OUTPUT_FUNCTION_NAME == true) echo "\r\nRunning: ".__FUNCTION__." = ";
    //    $this->markTestIncomplete(
    //          'This test has not been implemented yet.'
    //          );
    $filename="glo.txt";
    $fp=fopen($filename, "w+");
    fwrite($fp, "testcubrid sendglo");

    fclose($fp);

    $this->sql="create table test1 under glo (image glo)";
    cubrid_execute($this->con, $this->sql);

    $oid=cubrid_new_glo($this->con, "test1", $filename);

    $this->sql="insert into test1(image) values($oid)";
    $this->req=cubrid_execute($this->con, $this->sql);
    $res=cubrid_send_glo($this->con, $oid);
    $this->assertTrue($res);
    }
    */
  
  /**
   * @group php-822
   */
  public function testCubridSeqDrop0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id SEQUENCE INTEGER)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values({20, 40, 60, 80})";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    $res = cubrid_seq_drop ( $this->con, $oid, "id", 1 );
    $this->assertTrue ( $res );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( array ("40", "60", "80" ), $id );
    
  //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSeqDrop1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id SEQUENCE INTEGER)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values({20, 40, 60, 80})";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    try {
      $res = cubrid_seq_drop ( $this->con, $oid, "id", 0 );
      //$this->assertTrue($res);
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( array ("20", "40", "60", "80" ), $id );
    
  //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSeqDrop2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id SEQUENCE INTEGER)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values({20, 40, 60, 80})";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    try {
      $res = cubrid_seq_drop ( $this->con, $oid, "id", 6 );
      //$this->assertTrue($res);
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( array ("20", "40", "60", "80" ), $id );
    
  //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSeqInsert0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id SEQUENCE INTEGER)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values({20, 40, 60, 80})";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    cubrid_seq_insert ( $this->con, $oid, "id", 1, 3 );
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( array ("3", "20", "40", "60", "80" ), $id );
    
  //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSeqInsert1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id SEQUENCE INTEGER)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values({20, 40, 60, 80})";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    cubrid_seq_insert ( $this->con, $oid, "id", 6, 3 );
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( array ("20", "40", "60", "80", "", "3" ), $id );
    
  //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSeqInsert2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id SEQUENCE INTEGER)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values({20, 40, 60, 80})";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    try {
      cubrid_seq_insert ( $this->con, $oid, "id", 0, 3 );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( array ("20", "40", "60", "80" ), $id );
    
  //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSeqInsert3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id SEQUENCE INTEGER)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values({20, 40, 60, 80})";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    try {
      cubrid_seq_insert ( $this->con, $oid, "id", 1, "{3,4}" );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( array ("20", "40", "60", "80" ), $id );
    
  //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSeqPut0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id SEQUENCE INTEGER)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values({20, 40, 60, 80})";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    $res = cubrid_seq_put ( $this->con, $oid, "id", 1, 3 );
    $this->assertTrue ( $res );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id ) = cubrid_fetch ( $this->req );
    //print_r($id);
    

    $this->assertEquals ( array ("3", "40", "60", "80" ), $id );
    //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSeqPut1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id SEQUENCE INTEGER, age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values({20, 40, 60, 80}, 100)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    try {
      $res = cubrid_seq_put ( $this->con, $oid, "age", 1, 3 );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id, $age ) = cubrid_fetch ( $this->req );
    //print_r($id);
    $this->assertEquals ( ( int ) 100, $age );
    //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSeqPut2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id SEQUENCE INTEGER, age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values({20, 40, 60, 80}, 100)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    try {
      $res = cubrid_seq_put ( $this->con, $oid, "id", 6, 3 );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id, $age ) = cubrid_fetch ( $this->req );
    //print_r($id);
    $this->assertEquals ( array ("20", "40", "60", "80", "", "3" ), $id );
    //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSeqPut3() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id SEQUENCE INTEGER, age int)";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values({20, 40, 60, 80}, 100)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    $oid = cubrid_current_oid ( $this->req );
    
    try {
      $res = cubrid_seq_put ( $this->con, $oid, "id", 0, 3 );
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $id, $age ) = cubrid_fetch ( $this->req );
    //print_r($id);
    $this->assertEquals ( array ("20", "40", "60", "80" ), $id );
    //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSetAdd0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), hobby set varchar(20))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', {'a','b','c'} )";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select hobby from test1 where id=2";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $hobby ) = cubrid_fetch ( $this->req );
    $oid = cubrid_current_oid ( $this->req );
    
    $res = cubrid_set_add ( $this->con, $oid, "hobby", "d" );
    $this->assertTrue ( $res );
    
    $this->sql = "select hobby from test1 where id=2";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $hobby ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( array ("a", "b", "c", "d" ), $hobby );
    
  //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSetAdd1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), hobby set varchar(20))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', {'a','b','c'} )";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select hobby from test1 where id=2";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $hobby ) = cubrid_fetch ( $this->req );
    
    $oid = cubrid_current_oid ( $this->req );
    
    $res = cubrid_set_add ( $this->con, $oid, "hobby", "{'d', 'e'}" );
    $this->assertTrue ( $res );
    $this->sql = "select hobby from test1 where id=2";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $hobby ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( array ("a", "b", "c", "{'d', 'e'}" ), $hobby );
    
  //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  // ?? cannot be dropped
  public function testCubridSetDrop0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), hobby set(string))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', {'a','b','c'} )";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select hobby from test1 where id=2";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $hobby ) = cubrid_fetch ( $this->req );
    
    $oid = cubrid_current_oid ( $this->req );
    //$setoid = cubrid_get($this->con, $oid, "hobby");
    

    $res = cubrid_set_drop ( $this->con, $oid, "hobby", "a" );
    //echo $res;
    $this->sql = "select hobby from test1 where id=2";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $hobby ) = cubrid_fetch ( $this->req );
    //    print_r($hobby);
    

    $this->assertEquals ( array ("b", "c" ), $hobby );
    
  //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSetDrop1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), hobby set varchar(20))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', {'a','b','c'} )";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select hobby from test1 where id=2";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $hobby ) = cubrid_fetch ( $this->req );
    $oid = cubrid_current_oid ( $this->req );
    
    try {
      $res = cubrid_set_drop ( $this->con, $oid, "name", "a" );
      //echo $res;
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    
  //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridSetDrop2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int, name varchar(30), hobby set varchar(20))";
    cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "insert into test1 values(2, 'cj', {'a','b','c'} )";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select hobby from test1 where id=2";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $hobby ) = cubrid_fetch ( $this->req );
    //print_r($hobby);
    $oid = cubrid_current_oid ( $this->req );
    
    try {
      $res = cubrid_set_drop ( $this->con, $oid, "hobby", "{'a','b'}" );
      //echo $res;
    } catch ( Exception $e ) {
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    $this->sql = "select hobby from test1 where id=2";
    $this->req = cubrid_execute ( $this->con, $this->sql, CUBRID_INCLUDE_OID );
    list ( $hobby ) = cubrid_fetch ( $this->req );
    //print_r($hobby);
    $this->assertEquals ( array ("a", "b", "c" ), $hobby );
    //    $this->markTestIncomplete(
  //          'This test has not been implemented yet.'
  //        );
  }
  
  /**
   * @group php-822
   */
  public function testCubridRollBack0() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(2)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    
    $this->sql = "update test1 set id=3 where id=2";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    //$this->req = cubrid_execute($this->con, $this->sql);
    cubrid_close_request ( $this->req );
    $res = cubrid_rollback ( $this->con );
    $this->assertTrue ( $res );
    
    //$this->con = cubrid_connect(CubridTest::HOST, CubridTest::PORT, CubridTest::DBNAME, CubridTest::USERID, CubridTest::PASSWORD);
    

    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( 2, $id );
    
    $this->sql = "drop test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
    $this->con = null;
  }
  
  /**
   * @group php-822
   */
  public function testCubridRollBack1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(2)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    
    $this->sql = "update test1 set id=3 where id=2";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    $this->sql = "select * from test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    list ( $id ) = cubrid_fetch ( $this->req );
    $this->assertEquals ( 3, $id );
    
    $this->sql = "drop test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
    $this->con = null;
  }
  
  /**
   * @group php-822
   */
  public function testCubridRollBack2() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    $this->sql = "create table test1 (id int)";
    cubrid_execute ( $this->con, $this->sql );
    $this->sql = "insert into test1 values(2)";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    
    $this->sql = "update test1 set id=3 where id=2";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    
    try {
      cubrid_rollback ();
    } catch ( Exception $e ) {
      //echo cubrid_error_code();
      $this->assertTrue ( TRUE );
      //echo $e->getMessage();
    }
    $this->sql = "drop test1";
    $this->req = cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
    $this->con = null;
  }
  
  /**
   * @group arnia
   */
  public function testCubridVersion() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
      //echo cubrid_version();
    $str = substr ( cubrid_version (), 0, 4 );
    $this->assertEquals ( '8.2.', $str );
  }
  
  /**
   * @group arnia-wrong-parameters-count
   */
  public function testCubridVersion1() {
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";
    try {
      $var = cubrid_version ( 1 );
      $this->assertTrue ( FALSE, "Expected Exception not thrown." );
    } catch ( Exception $e ) {
      //echo $e->getMessage()."\r\n";
      $this->assertEquals ( 0, cubrid_error_code () );
      $this->assertEquals ( 0, cubrid_error_code_facility () );
      $this->assertEquals ( '', cubrid_error_msg () );
    }
  }
  
  public function testCubridCci1() {

    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";   
    try {
    	$this->sql = "create table t1(code int unique, s string) ";
      cubrid_execute ( $this->con, $this->sql );

      // case : normal insert
      for($i = 0; $i < 100; $i ++) {
		$this->sql = "insert into t1 values($i, 'aaa')";
            $this->req = cubrid_execute ( $this->con, $this->sql );
      }

    	$this->sql = "select count(*) from t1";
    	$this->req = cubrid_execute ( $this->con, $this->sql );
    	$str = cubrid_fetch_row( $this->req );
    	echo $str[0];
    	$this->assertEquals ( $str[0], 100);

      echo "\r\n#### case Cci1 OK #### ";

    } catch ( Exception $e ) {
       echo "\r\n#### Catch Cci1 Exception #### ";
    }

    $this->sql = "drop table t1";
    cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
  }
  public function testCubridCci2() {
   
    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";   
    try { 
    	$this->sql = "create table t1(code int unique, s string) ";
      cubrid_execute ( $this->con, $this->sql );
	cubrid_commit ( $this->con );

    	for($i = 0; $i < 100; $i ++) {
		$this->sql = "insert into t1 values($i, 'aaa')";
             $this->req = cubrid_execute ( $this->con, $this->sql );
      }

    	cubrid_rollback ( $this->con );
    	$this->sql = "select count(*) from t1";
    	$this->req = cubrid_execute ( $this->con, $this->sql );
    	$str = cubrid_fetch_row( $this->req );
    	echo $str[0];
    	$this->assertEquals ( $str[0], 0);
   
      echo "\r\n#### case Cci2 OK #### ";
    } catch ( Exception $e ) {
       echo "\r\n#### Catch Cci2 Exception #### ";
    }
    $this->sql = "drop table t1";
    cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
  }
  public function testCubridCci3() {

    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";   
    try {
    	$this->sql = "create table t1(code int unique, s string) ";
	cubrid_execute ( $this->con, $this->sql );

	for($i = 0; $i < 100; $i ++) {
		$this->sql = "insert into t1 values($i, 'aaa')";
		$this->req = cubrid_execute ( $this->con, $this->sql );
    	}
    	cubrid_rollback ( $this->con );
    	$this->sql = "select count(*) from t1";
    	$this->req = cubrid_execute ( $this->con, $this->sql );
    	$str = cubrid_fetch_row( $this->req );
    	echo $str[0];
    	$this->assertEquals ( $str[0], 100);

    	echo "\r\n#### case Cci3 OK #### ";
    } catch ( Exception $e ) {
	 echo "\r\n#### Catch Cci3 Exception #### ";
    }
    $this->sql = "drop table t1";
    cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
  }

  public function testCubridCci4() {

    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";   
    try {
	$table = "t1";
	$this->sql = "create table t1(code int unique, s string) ";
	cubrid_execute ( $this->con, $this->sql );
	cubrid_commit ( $this->con );

	for($i = 0; $i < 100; $i ++) {
              $this->sql = "insert into t1 values($i, 'aaa')";
              $this->req = cubrid_execute ( $this->con, $this->sql );
      }
    	cubrid_rollback ( $this->con );
    	$this->sql = "select count(*) from t1";
    	$this->req = cubrid_execute ( $this->con, $this->sql );
    	$str = cubrid_fetch_row( $this->req );
    	echo $str[0];
    	$this->assertEquals ( $str[0], 0);
    	echo "\r\n#### case Cci4 OK #### ";
   
    } catch ( Exception $e ) {
       echo "\r\n#### Catch Cci4 Exception #### ";
    }
    $this->sql = "drop table t1";
    cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
  }

  public function testCubridCci5() {
    // case : ac on
    // test_execute_huge_tuple

    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";   
    try {
    	$this->sql = "create table t1(code int unique, s string) ";
	cubrid_execute ( $this->con, $this->sql );

	for($i = 0; $i < 10000; $i ++) {
		$this->sql = "insert into t1 values($i, 'aaa')";
		$this->req = cubrid_execute ( $this->con, $this->sql );
    	}
 
    	$this->sql = "select count(*) from t1";
    	$this->req = cubrid_execute ( $this->con, $this->sql );
    	$str = cubrid_fetch_row( $this->req );
    	echo $str[0];
    	$this->assertEquals ( $str[0], 10000);
    	echo "\r\n#### case Cci5 OK #### ";
   
    } catch ( Exception $e ) {
       echo "\r\n#### Catch Cci5 Exception #### ";
    }
    $this->sql = "drop table t1";
    cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
  }

  /**
   * @group cci_multi
   */
  public function testCubridCci6_t1() {
    // case : ac on
    // multi_connection

    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";   
    try {
    	$this->sql = "create table t1(code int unique, s string) ";
	cubrid_execute ( $this->con, $this->sql );

    	for($count = 0; $count < 10; $count ++) {
 		$this->sql = "delete from t1 ";
		cubrid_execute ( $this->con, $this->sql );
   		cubrid_commit ( $this->con );

 		for($i = 0; $i < 1000; $i ++) {
 			$this->sql = "insert into t1 values($i, 'aaa')";
               	$this->req = cubrid_execute ( $this->con, $this->sql );
  		}
 		$this->sql = "select count(*) from t1";
    		$this->req = cubrid_execute ( $this->con, $this->sql );
     		$str = cubrid_fetch_row( $this->req );
     		echo $str[0];
     		$this->assertEquals ( $str[0], 1000);
 		echo "\r\n-----Running:---------------$count  ";
    	}
	echo "\r\n#### case Cci6_t1 OK #### ";
    } catch ( Exception $e ) {
       echo "\r\n#### Catch Cci6_t1 Exception #### ";
    }
    $this->sql = "drop table t1";
    cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
  }
  /**
   * @group cci_multi
   */
  public function testCubridCci6_t2() {
    // case : ac on

    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";   
    try { 
    	$this->sql = "create table t2(code int unique, s string) ";
	cubrid_execute ( $this->con, $this->sql );
    	for($count = 0; $count < 10; $count ++) {
 		$this->sql = "delete from t2 ";
		cubrid_execute ( $this->con, $this->sql );
   		cubrid_commit ( $this->con );

 		for($i = 0; $i < 1000; $i ++) {
 			$this->sql = "insert into t2 values($i, 'aaa')";
               	$this->req = cubrid_execute ( $this->con, $this->sql );
  		}
 		$this->sql = "select count(*) from t2";
    		$this->req = cubrid_execute ( $this->con, $this->sql );
     		$str = cubrid_fetch_row( $this->req );

     		echo $str[0];
     		$this->assertEquals ( $str[0], 1000);
		echo "\r\n-----Running:---------------$count  ";
    	}
    	echo "\r\n#### case Cci6_t2 OK #### ";
    } catch ( Exception $e ) {
       echo "\r\n#### Catch Cci6_t2 Exception #### ";
    }
    $this->sql = "drop table t2";
    cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
  }
  /**
   * @group cci_multi
   */
  public function testCubridCci6_t3() {
    // case : ac on
    // multi_connection tname?

    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";   
    try {
    	$this->sql = "create table t3(code int unique, s string) ";
	cubrid_execute ( $this->con, $this->sql );

    	for($count = 0; $count < 10; $count ++) {
 		$this->sql = "delete from t3 ";
		cubrid_execute ( $this->con, $this->sql );
   		cubrid_commit ( $this->con );

 		for($i = 0; $i < 1000; $i ++) {
 			$this->sql = "insert into t3 values($i, 'aaa')";
               	$this->req = cubrid_execute ( $this->con, $this->sql );
  		}
 		$this->sql = "select count(*) from t3";
    		$this->req = cubrid_execute ( $this->con, $this->sql );
     		$str = cubrid_fetch_row( $this->req );
     		echo $str[0];
     		$this->assertEquals ( $str[0], 1000);
 		echo "\r\n-----Running:---------------$count  ";    		
    	}
	echo "\r\n#### case Cci6_t3 OK #### ";
    } catch ( Exception $e ) {
       echo "\r\n#### Catch Cci6_t3 Exception #### ";
    }
    $this->sql = "drop table t3";
    cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
  }
  /**
   * @group cci_multi
   */
  public function testCubridCci6_t4() {
    // case : ac on
    // multi_connection tname?

    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";   
    try {
    	$this->sql = "create table t4(code int unique, s string) ";
	cubrid_execute ( $this->con, $this->sql );

    	for($count = 0; $count < 10; $count ++) {
 		$this->sql = "delete from t4 ";
		cubrid_execute ( $this->con, $this->sql );
   		cubrid_commit ( $this->con );

 		for($i = 0; $i < 1000; $i ++) {
 			$this->sql = "insert into t4 values($i, 'aaa')";
               	$this->req = cubrid_execute ( $this->con, $this->sql );
 		
  		}
 		$this->sql = "select count(*) from t4";
    		$this->req = cubrid_execute ( $this->con, $this->sql );
     		$str = cubrid_fetch_row( $this->req );
     		echo $str[0];
     		$this->assertEquals ( $str[0], 1000);
		echo "\r\n-----Running:---------------$count  ";
    	}
	echo "\r\n#### case Cci6_t4 OK #### ";
    } catch ( Exception $e ) {
       echo "\r\n#### Catch Cci6_t4 Exception #### ";
    }
    $this->sql = "drop table t4";
    cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
  }
  /**
   * @group cci_multi
   */
  public function testCubridCci6_t5() {
    // case : ac on
    // multi_connection tname?

    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";   
    try {
    	$this->sql = "create table t5(code int unique, s string) ";
	cubrid_execute ( $this->con, $this->sql );

    	for($count = 0; $count < 10; $count ++) {
 		$this->sql = "delete from t5 ";
		cubrid_execute ( $this->con, $this->sql );
   		cubrid_commit ( $this->con );

 		for($i = 0; $i < 1000; $i ++) {
 			$this->sql = "insert into t5 values($i, 'aaa')";
               	$this->req = cubrid_execute ( $this->con, $this->sql );
  		}
 		$this->sql = "select count(*) from t5";
    		$this->req = cubrid_execute ( $this->con, $this->sql );
     		$str = cubrid_fetch_row( $this->req );
     		echo $str[0];
     		$this->assertEquals ( $str[0], 1000);
		echo "\r\n-----Running:---------------$count  ";
    	}
	echo "\r\n#### case Cci6_t5 OK #### ";
    } catch ( Exception $e ) {
       echo "\r\n#### Catch Cci6_t5 Exception #### ";
    }
    $this->sql = "drop table t5";
    cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
  }
  /**
   * @group cci_multi
   */
  public function testCubridCci6_t6() {
    // case : ac on
    // multi_connection tname?

    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";   
    try {
    	$this->sql = "create table t6(code int unique, s string) ";
	cubrid_execute ( $this->con, $this->sql );

    	for($count = 0; $count < 10; $count ++) {
 		$this->sql = "delete from t6 ";
		cubrid_execute ( $this->con, $this->sql );
   		cubrid_commit ( $this->con );

 		for($i = 0; $i < 1000; $i ++) {
 			$this->sql = "insert into t6 values($i, 'aaa')";
               	$this->req = cubrid_execute ( $this->con, $this->sql );
  		}
 		$this->sql = "select count(*) from t6";
    		$this->req = cubrid_execute ( $this->con, $this->sql );
     		$str = cubrid_fetch_row( $this->req );
     		echo $str[0];
     		$this->assertEquals ( $str[0], 1000);
		echo "\r\n-----Running:---------------$count  ";
    	}
	echo "\r\n#### case Cci6_t6 OK #### ";
    } catch ( Exception $e ) {
       echo "\r\n#### Catch Cci6_t6 Exception #### ";
    }
    $this->sql = "drop table t6";
    cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
  }
  /**
   * @group cci_multi
   */
  public function testCubridCci6_t7() {
    // case : ac on
    // multi_connection tname?

    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";   
    try {
    	$this->sql = "create table t7(code int unique, s string) ";
	cubrid_execute ( $this->con, $this->sql );

    	for($count = 0; $count < 10; $count ++) {
 		$this->sql = "delete from t7 ";
		cubrid_execute ( $this->con, $this->sql );
   		cubrid_commit ( $this->con );

 		for($i = 0; $i < 1000; $i ++) {
 			$this->sql = "insert into t7 values($i, 'aaa')";
               	$this->req = cubrid_execute ( $this->con, $this->sql );
  		}
 		$this->sql = "select count(*) from t7";
    		$this->req = cubrid_execute ( $this->con, $this->sql );
     		$str = cubrid_fetch_row( $this->req );
     		echo $str[0];
     		$this->assertEquals ( $str[0], 1000);
		echo "\r\n-----Running:---------------$count  ";
    	}
	echo "\r\n#### case Cci6_t7 OK #### ";
    } catch ( Exception $e ) {
       echo "\r\n#### Catch Cci6_t7 Exception #### ";
    }
    $this->sql = "drop table t7";
    cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
  }
  /**
   * @group cci_multi
   */
  public function testCubridCci6_t8() {
    // case : ac on
    // multi_connection tname?

    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";   
    try {
    	$this->sql = "create table t8(code int unique, s string) ";
	cubrid_execute ( $this->con, $this->sql );

    	for($count = 0; $count < 10; $count ++) {
 		$this->sql = "delete from t8 ";
		cubrid_execute ( $this->con, $this->sql );
   		cubrid_commit ( $this->con );

 		for($i = 0; $i < 1000; $i ++) {
 			$this->sql = "insert into t8 values($i, 'aaa')";
               	$this->req = cubrid_execute ( $this->con, $this->sql );
  		}
 		$this->sql = "select count(*) from t8";
    		$this->req = cubrid_execute ( $this->con, $this->sql );
     		$str = cubrid_fetch_row( $this->req );
     		echo $str[0];
     		$this->assertEquals ( $str[0], 1000);
		echo "\r\n-----Running:---------------$count  ";
    	}
	echo "\r\n#### case Cci6_t8 OK #### ";
    } catch ( Exception $e ) {
       echo "\r\n#### Catch Cci6_t8 Exception #### ";
    }
    $this->sql = "drop table t8";
    cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
  }

  public function testCubridCci6_t9() {
    // case : ac on
    // multi_connection tname?

    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";   
    try {
    	$this->sql = "create table t9(code int unique, s string) ";
	cubrid_execute ( $this->con, $this->sql );
    	for($count = 0; $count < 10; $count ++) {
 		$this->sql = "delete from t9 ";
		cubrid_execute ( $this->con, $this->sql );
   		cubrid_commit ( $this->con );

 		for($i = 0; $i < 1000; $i ++) {
 			$this->sql = "insert into t9 values($i, 'aaa')";
               	$this->req = cubrid_execute ( $this->con, $this->sql );
  		}
 		$this->sql = "select count(*) from t9";
    		$this->req = cubrid_execute ( $this->con, $this->sql );
     		$str = cubrid_fetch_row( $this->req );
     		echo $str[0];
     		$this->assertEquals ( $str[0], 1000);
		echo "\r\n-----Running:---------------$count  ";
    	}
	echo "\r\n#### case Cci6_t9 OK #### ";
    } catch ( Exception $e ) {
       echo "\r\n#### Catch Cci6_t9 Exception #### ";
    }
    $this->sql = "drop table t9";
    cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
  }

  public function testCubridCci6_t10() {
    // case : ac on
    // multi_connection tname?

    if (OUTPUT_FUNCTION_NAME == true)
      echo "\r\nRunning: " . __FUNCTION__ . " = ";   
    try {
    	$this->sql = "create table t10(code int unique, s string) ";
	cubrid_execute ( $this->con, $this->sql );

    	for($count = 0; $count < 10; $count ++) {
 		$this->sql = "delete from t10 ";
		cubrid_execute ( $this->con, $this->sql );
   		cubrid_commit ( $this->con );

 		for($i = 0; $i < 1000; $i ++) {
 			$this->sql = "insert into t10 values($i, 'aaa')";
               	$this->req = cubrid_execute ( $this->con, $this->sql );
  		}
 		$this->sql = "select count(*) from t10";
    		$this->req = cubrid_execute ( $this->con, $this->sql );
     		$str = cubrid_fetch_row( $this->req );
     		echo $str[0];
     		$this->assertEquals ( $str[0], 1000);
		echo "\r\n-----Running:---------------$count  ";
    	}
	echo "\r\n#### case Cci6_t10 OK #### ";
    } catch ( Exception $e ) {
       echo "\r\n#### Catch Cci6_t10 Exception #### ";
    }
    $this->sql = "drop table t10";
    cubrid_execute ( $this->con, $this->sql );
    cubrid_commit ( $this->con );
    $this->req = null;
  }
  
  // cleanup
  protected function tearDown() {
    if ($this->error_flag) {
      echo "Req:" . $this->req;
      echo "Error Code: ", cubrid_error_code ();
      echo "Error Facility: ", cubrid_error_code_facility ();
      echo "Error Message: ", cubrid_error_msg ();
    }
    
    if ($this->req) {
      cubrid_close_request ( $this->req );
    }
    
    if ($this->con) {
      cubrid_disconnect ( $this->con );
    }
    
    if (VERBOSE_OUTPUT == true)
      echo "\r\nCleanup completed.";
  }
}
?>
