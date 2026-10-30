--TEST--
cubrid_field_flag
--SKIPIF--
<?php
require_once('skipif.inc');
require_once('skipifconnectfailure.inc');
?>
--FILE--
<?php
//table contains primary kay and foreign key
include_once("connect.inc");
$conn = cubrid_connect_with_url($connect_url, $user, $passwd);
cubrid_execute($conn,"drop table if EXISTS track;");
cubrid_execute($conn,"drop table if EXISTS album;");
cubrid_execute($conn,"CREATE TABLE album(id_1 char(10) , id_2 char(10) , id_3 char(10) , id_4 char(10) , id_5 char(10) ,
 id_6 char(10) , id_7 char(10) , id_8 char(10) , id_9 char(10) , id_10 char(10) ,
 id_11 char(10) , id_12 char(10) , id_13 char(10) , id_14 char(10) , id_15 char(10) ,
 id_16 char(10) , id_17 char(10) , id_18 char(10) , id_19 char(10) , id_20 char(10) ,
 id_21 char(10) , id_22 char(10) , id_23 char(10) , id_24 char(10) , id_25 char(10) ,
 id_26 char(10) , id_27 char(10) , id_28 char(10) , id_29 char(10) , id_30 char(10) ,
 id_31 char(10) , id_32 char(10) , id_33 char(10) , id_34 char(10) , id_35 char(10) ,
 id_36 char(10) , id_37 char(10) , id_38 char(10) , id_39 char(10) , id_40 char(10) ,
 id_41 char(10) , id_42 char(10) , id_43 char(10) , id_44 char(10) , id_45 char(10) ,
 id_46 char(10) , id_47 char(10) , id_48 char(10) , id_49 char(10) , id_50 char(10) ,
 id_51 char(10) , id_52 char(10) , id_53 char(10) , id_54 char(10) , id_55 char(10) ,
 id_56 char(10) , id_57 char(10) , id_58 char(10) , id_59 char(10) , id_60 char(10) ,
 id_61 char(10) , id_62 char(10) , id_63 char(10) , id_64 char(10) , id_65 char(10) ,
 title varchar(100), artist  VARCHAR(100), CONSTRAINT \"pk_album_id\" PRIMARY KEY (id_1, id_2, id_3, id_4, id_5, id_6, id_7, id_8, id_9, id_10,id_11, id_12, id_13, id_14, id_15, id_16, id_17, id_18, id_19, id_20,
       id_21, id_22, id_23, id_24, id_25, id_26, id_27, id_28, id_29, id_30,
       id_31, id_32, id_33, id_34, id_35, id_36, id_37, id_38, id_39, id_40,
       id_41, id_42, id_43, id_44, id_45, id_46, id_47, id_48, id_49, id_50,
       id_51, id_52, id_53, id_54, id_55, id_56, id_57, id_58, id_59, id_60,
       id_61, id_62, id_63, id_64, id_65 ));");
cubrid_execute($conn,"CREATE TABLE track(
  album_1 char(10) , album_2 char(10) , album_3 char(10) , album_4 char(10) , album_5 char(10) ,
 album_6 char(10) , album_7 char(10) , album_8 char(10) , album_9 char(10) , album_10 char(10) ,
 album_11 char(10) , album_12 char(10) , album_13 char(10) , album_14 char(10) , album_15 char(10) ,
 album_16 char(10) , album_17 char(10) , album_18 char(10) , album_19 char(10) , album_20 char(10) ,
 album_21 char(10) , album_22 char(10) , album_23 char(10) , album_24 char(10) , album_25 char(10) ,
 album_26 char(10) , album_27 char(10) , album_28 char(10) , album_29 char(10) , album_30 char(10) ,
 album_31 char(10) , album_32 char(10) , album_33 char(10) , album_34 char(10) , album_35 char(10) ,
 album_36 char(10) , album_37 char(10) , album_38 char(10) , album_39 char(10) , album_40 char(10) ,
 album_41 char(10) , album_42 char(10) , album_43 char(10) , album_44 char(10) , album_45 char(10) ,
 album_46 char(10) , album_47 char(10) , album_48 char(10) , album_49 char(10) , album_50 char(10) ,
 album_51 char(10) , album_52 char(10) , album_53 char(10) , album_54 char(10) , album_55 char(10) ,
 album_56 char(10) , album_57 char(10) , album_58 char(10) , album_59 char(10) , album_60 char(10) ,
 album_61 char(10) , album_62 char(10) , album_63 char(10) , album_64 char(10) , album_65 char(10) ,
  dsk INTEGER,
  posn INTEGER,
  song VARCHAR(255),
  FOREIGN KEY (album_1, album_2, album_3, album_4, album_5, album_6, album_7, album_8, album_9, album_10,
               album_11, album_12, album_13, album_14, album_15, album_16, album_17, album_18, album_19, album_20,
album_21, album_22, album_23, album_24, album_25, album_26, album_27, album_28, album_29, album_30,
album_31, album_32, album_33, album_34, album_35, album_36, album_37, album_38, album_39, album_40,
album_41, album_42, album_43, album_44, album_45, album_46, album_47, album_48, album_49, album_50,
album_51, album_52, album_53, album_54, album_55, album_56, album_57, album_58, album_59, album_60,
album_61, album_62, album_63, album_64, album_65)
REFERENCES album(id_1, id_2, id_3, id_4, id_5, id_6, id_7, id_8, id_9, id_10,
                id_11, id_12, id_13, id_14, id_15, id_16, id_17, id_18, id_19, id_20,
id_21, id_22, id_23, id_24, id_25, id_26, id_27, id_28, id_29, id_30,
id_31, id_32, id_33, id_34, id_35, id_36, id_37, id_38, id_39, id_40,
id_41, id_42, id_43, id_44, id_45, id_46, id_47, id_48, id_49, id_50,
id_51, id_52, id_53, id_54, id_55, id_56, id_57, id_58, id_59, id_60,
id_61, id_62, id_63, id_64, id_65)
);");

print("#####positive example#####\n");
$result=cubrid_execute($conn,"select * from  album");
var_dump(cubrid_fetch_row($result) );
$col_num = cubrid_num_cols($result);

for($i = 0; $i < $col_num; $i++) {
   printf("%-30s %s\n", cubrid_field_name($result, $i), cubrid_field_flags($result, $i)); 
}
cubrid_close_request($result);


$result=cubrid_execute($conn,"select * from track");
var_dump(cubrid_fetch_row($result) );
$col_num = cubrid_num_cols($result);

for($i = 0; $i < $col_num; $i++) {
   printf("%-30s %s\n", cubrid_field_name($result, $i), cubrid_field_flags($result, $i));
}
cubrid_close_request($result);


cubrid_disconnect($conn);

print "Finished!\n";
?>
--CLEAN--
--EXPECTF--
#####positive example#####
bool(false)
id_1                           not_null primary_key unique_key
id_2                           not_null primary_key unique_key
id_3                           not_null primary_key unique_key
id_4                           not_null primary_key unique_key
id_5                           not_null primary_key unique_key
id_6                           not_null primary_key unique_key
id_7                           not_null primary_key unique_key
id_8                           not_null primary_key unique_key
id_9                           not_null primary_key unique_key
id_10                          not_null primary_key unique_key
id_11                          not_null primary_key unique_key
id_12                          not_null primary_key unique_key
id_13                          not_null primary_key unique_key
id_14                          not_null primary_key unique_key
id_15                          not_null primary_key unique_key
id_16                          not_null primary_key unique_key
id_17                          not_null primary_key unique_key
id_18                          not_null primary_key unique_key
id_19                          not_null primary_key unique_key
id_20                          not_null primary_key unique_key
id_21                          not_null primary_key unique_key
id_22                          not_null primary_key unique_key
id_23                          not_null primary_key unique_key
id_24                          not_null primary_key unique_key
id_25                          not_null primary_key unique_key
id_26                          not_null primary_key unique_key
id_27                          not_null primary_key unique_key
id_28                          not_null primary_key unique_key
id_29                          not_null primary_key unique_key
id_30                          not_null primary_key unique_key
id_31                          not_null primary_key unique_key
id_32                          not_null primary_key unique_key
id_33                          not_null primary_key unique_key
id_34                          not_null primary_key unique_key
id_35                          not_null primary_key unique_key
id_36                          not_null primary_key unique_key
id_37                          not_null primary_key unique_key
id_38                          not_null primary_key unique_key
id_39                          not_null primary_key unique_key
id_40                          not_null primary_key unique_key
id_41                          not_null primary_key unique_key
id_42                          not_null primary_key unique_key
id_43                          not_null primary_key unique_key
id_44                          not_null primary_key unique_key
id_45                          not_null primary_key unique_key
id_46                          not_null primary_key unique_key
id_47                          not_null primary_key unique_key
id_48                          not_null primary_key unique_key
id_49                          not_null primary_key unique_key
id_50                          not_null primary_key unique_key
id_51                          not_null primary_key unique_key
id_52                          not_null primary_key unique_key
id_53                          not_null primary_key unique_key
id_54                          not_null primary_key unique_key
id_55                          not_null primary_key unique_key
id_56                          not_null primary_key unique_key
id_57                          not_null primary_key unique_key
id_58                          not_null primary_key unique_key
id_59                          not_null primary_key unique_key
id_60                          not_null primary_key unique_key
id_61                          not_null primary_key unique_key
id_62                          not_null primary_key unique_key
id_63                          not_null primary_key unique_key
id_64                          not_null primary_key unique_key
id_65                          not_null primary_key unique_key
title                          
artist                         
bool(false)
album_1                        foreign_key
album_2                        foreign_key
album_3                        foreign_key
album_4                        foreign_key
album_5                        foreign_key
album_6                        foreign_key
album_7                        foreign_key
album_8                        foreign_key
album_9                        foreign_key
album_10                       foreign_key
album_11                       foreign_key
album_12                       foreign_key
album_13                       foreign_key
album_14                       foreign_key
album_15                       foreign_key
album_16                       foreign_key
album_17                       foreign_key
album_18                       foreign_key
album_19                       foreign_key
album_20                       foreign_key
album_21                       foreign_key
album_22                       foreign_key
album_23                       foreign_key
album_24                       foreign_key
album_25                       foreign_key
album_26                       foreign_key
album_27                       foreign_key
album_28                       foreign_key
album_29                       foreign_key
album_30                       foreign_key
album_31                       foreign_key
album_32                       foreign_key
album_33                       foreign_key
album_34                       foreign_key
album_35                       foreign_key
album_36                       foreign_key
album_37                       foreign_key
album_38                       foreign_key
album_39                       foreign_key
album_40                       foreign_key
album_41                       foreign_key
album_42                       foreign_key
album_43                       foreign_key
album_44                       foreign_key
album_45                       foreign_key
album_46                       foreign_key
album_47                       foreign_key
album_48                       foreign_key
album_49                       foreign_key
album_50                       foreign_key
album_51                       foreign_key
album_52                       foreign_key
album_53                       foreign_key
album_54                       foreign_key
album_55                       foreign_key
album_56                       foreign_key
album_57                       foreign_key
album_58                       foreign_key
album_59                       foreign_key
album_60                       foreign_key
album_61                       foreign_key
album_62                       foreign_key
album_63                       foreign_key
album_64                       foreign_key
album_65                       foreign_key
dsk                            
posn                           
song                           
Finished!
