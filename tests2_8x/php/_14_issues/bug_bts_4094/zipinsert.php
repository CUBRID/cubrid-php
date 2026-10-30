<?php
  $file_name = "zipcode.csv";
  $db["host"] = "test-db-server";
  $db["port"] = 33000;
  $db["dbname"] = "testdb";
  $db["username"] = "dba";
  $db["password"] = "";
  $db["table"] = "zipcode";

  $fh = fopen ($file_name, "r");
  if (!is_resource ($fh)) {
    echo ("Error: Cannot open file.\n");
    exit (0);
  }
  $count = 0;
  while (!feof ($fh)) {
    $tmp = fgets ($fh);
    if ($tmp === false) break;
    $buff = explode (",", trim ($tmp));
    if (preg_match ("/^[0-9]/", $buff[0]) > 0) {
      $buff[4] = preg_replace (array ("/\(/", "/\)/"), array ('', ''), $buff[4]);
      $addr_list[] = $buff;
    }
    $count++;
  }
  fclose ($fh);
  printf ("%d records readed.\n", $count-1);

  $dh = cubrid_connect ($db["host"], $db["port"], $db["dbname"],
                        $db["username"], $db["password"]);
  if ($dh === false) {
    sprintf ("DB Error %d (%d): %s\n",
             cubrid_error_code (),
             cubrid_error_code_facility (),
             cubrid_error_msg ());
    exit (0);
  }

  $result = cubrid_execute ($dh,
                           "SELECT count (\"class_name\") AS \"cnt\" ".
                           "FROM \"db_class\" WHERE ".
                           "\"class_name\" = '".$db["table"]."';");
  if ($result === false) {
    printf ("DB Error %d (%d): %s\n",
            cubrid_error_code (),
            cubrid_error_code_facility (),
            cubrid_error_msg ());
    cubrid_rollback ($dh);
    cubrid_disconnect ($dh);
    exit (0);
  }
  cubrid_commit ($dh);
  $is_exist = cubrid_fetch ($result, CUBRID_NUM);
  if ($is_exist === false) {
    printf ("DB Error %d (%d): %s\n",
            cubrid_error_code (),
            cubrid_error_code_facility (),
            cubrid_error_msg ());
    cubrid_close_request ($result);
    cubrid_disconnect ($dh);
    exit (0);
  }
  cubrid_close_request ($result);
  $is_exist = ($is_exist[0])?true:false;
  if ($is_exist === true) {
    $result = cubrid_execute ($dh,
      "DROP TABLE \"".$db["table"]."\";");
    if ($result === false) {
      printf ("DB Error %d (%d): %s\n",
              cubrid_error_code (),
              cubrid_error_code_facility (),
              cubrid_error_msg ());
      cubrid_rollback ($dh);
      cubrid_disconnect ($dh);
      exit (0);
    }
    cubrid_close_request ($result);
    cubrid_commit ($dh);
    $is_exist = false;
  }
  if ($is_exist === false) {
    $result = cubrid_execute ($dh,
      "CREATE TABLE \"".$db["table"]."\" (
         \"index\"   INTEGER NOT NULL,
         \"addr1\"   CHARACTER VARYING(12) NOT NULL,
         \"addr2\"   CHARACTER VARYING(30) NOT NULL,
         \"addr3\"   CHARACTER VARYING(64) NOT NULL,
         \"addr4\"   CHARACTER VARYING(24) NOT NULL,
         \"zipcode\" CHARACTER(7) NOT NULL
       );");
    if ($result === false) {
      printf ("DB Error %d (%d): %s\n",
              cubrid_error_code (),
              cubrid_error_code_facility (),
              cubrid_error_msg ());
      cubrid_rollback ($dh);
      cubrid_disconnect ($dh);
      exit (0);
    }
    cubrid_close_request ($result);
    cubrid_commit ($dh);
    $result = cubrid_execute ($dh,
      "CREATE UNIQUE INDEX \"".$db["table"]."_idx\"
         ON \"".$db["table"]."\" (\"index\" ASC);");
    if ($result === false) {
      printf ("Cannot create index.\n");
      cubrid_rollback ($dh);
    }
    else {
      cubrid_close_request ($result);
      cubrid_commit ($dh);
    }
  }

  $error_count = 0;
  $inserted = 0;
  foreach ($addr_list as $val) {
    $result = cubrid_execute ($dh,
      sprintf ("INSERT INTO \"%s\" (\"index\", \"addr1\", \"addr2\",
                  \"addr3\", \"addr4\", \"zipcode\") VALUES
                  (%d, '%s', '%s', '%s', '%s', '%s');", $db["table"],
        $val[5], $val[1], $val[2], $val[3], $val[4], $val[0]));
    if ($result === false) {
      $error_count++;
      printf ("Insert Error! Index: %d, Error Count: %d\n",
              $val[5], $error_count);
      continue;
    }
    $inserted++;
    if ($inserted%1000 === 0) {
      printf ("%d record inserted.\n", $inserted);
    }
    cubrid_close_request ($result);
  }
  cubrid_commit ($dh);
  cubrid_disconnect ($dh);
  printf ("Done. %d record inserted, %d errors.\n", $inserted, $error_count);
?>
