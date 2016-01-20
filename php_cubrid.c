/*
 * Copyright (C) 2008 Search Solution Corporation. All rights reserved by Search Solution.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 *
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * - Neither the name of the <ORGANIZATION> nor the names of its contributors
 *   may be used to endorse or promote products derived from this software without
 *   specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA,
 * OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY
 * OF SUCH DAMAGE.
 *
 */

#define _CRT_SECURE_NO_WARNINGS

/************************************************************************
* IMPORTED SYSTEM HEADER FILES
************************************************************************/

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "php_ini.h"
#include "php_globals.h"
#include "ext/standard/info.h"
#include "ext/standard/php_string.h"

#include "zend_exceptions.h"

#ifdef PHP_WIN32
#include <winsock.h>
#endif

/************************************************************************
* OTHER IMPORTED HEADER FILES
************************************************************************/

#include "php_cubrid.h"
#include "php_cubrid_version.h"
#include <cas_cci.h>

/************************************************************************
* PRIVATE DEFINITIONS
************************************************************************/

#if PHP_MINOR_VERSION < 3
#define zend_parse_parameters_none() zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "")
#endif

#define MAX_SERIAL_PRECISION        38
/* EXECUTE */
#define CUBRID_INCLUDE_OID			1
#define CUBRID_ASYNC				2

/* ARRAY */
typedef enum
{
    CUBRID_NUM = 1,
    CUBRID_ASSOC = 2,
    CUBRID_BOTH = CUBRID_NUM | CUBRID_ASSOC,
    CUBRID_OBJECT = 4,
} T_CUBRID_ARRAY_TYPE;

/* CURSOR ORIGIN */
typedef enum
{
    CUBRID_CURSOR_FIRST = CCI_CURSOR_FIRST,
    CUBRID_CURSOR_CURRENT = CCI_CURSOR_CURRENT,
    CUBRID_CURSOR_LAST = CCI_CURSOR_LAST,
} T_CUBRID_CURSOR_ORIGIN;

/* CURSOR RESULT */
#define CUBRID_CURSOR_SUCCESS		1
#define CUBRID_NO_MORE_DATA		0
#define CUBRID_CURSOR_ERROR		-1

/* SCHEMA */
#define CUBRID_SCH_CLASS		CCI_SCH_CLASS
#define CUBRID_SCH_VCLASS		CCI_SCH_VCLASS
#define CUBRID_SCH_QUERY_SPEC		CCI_SCH_QUERY_SPEC
#define CUBRID_SCH_ATTRIBUTE		CCI_SCH_ATTRIBUTE
#define CUBRID_SCH_CLASS_ATTRIBUTE	CCI_SCH_CLASS_ATTRIBUTE
#define CUBRID_SCH_METHOD		CCI_SCH_METHOD
#define CUBRID_SCH_CLASS_METHOD		CCI_SCH_CLASS_METHOD
#define CUBRID_SCH_METHOD_FILE		CCI_SCH_METHOD_FILE
#define CUBRID_SCH_SUPERCLASS		CCI_SCH_SUPERCLASS
#define CUBRID_SCH_SUBCLASS		CCI_SCH_SUBCLASS
#define CUBRID_SCH_CONSTRAINT		CCI_SCH_CONSTRAINT
#define CUBRID_SCH_TRIGGER		CCI_SCH_TRIGGER
#define CUBRID_SCH_CLASS_PRIVILEGE	CCI_SCH_CLASS_PRIVILEGE
#define CUBRID_SCH_ATTR_PRIVILEGE	CCI_SCH_ATTR_PRIVILEGE
#define CUBRID_SCH_DIRECT_SUPER_CLASS	CCI_SCH_DIRECT_SUPER_CLASS
#define CUBRID_SCH_PRIMARY_KEY		CCI_SCH_PRIMARY_KEY

/* ERROR FACILITY */
typedef enum
{
    CUBRID_FACILITY_DBMS = 1,
    CUBRID_FACILITY_CAS,
    CUBRID_FACILITY_CCI,
    CUBRID_FACILITY_CLIENT,
} T_FACILITY_CODE;

/* error codes */
#define CUBRID_ER_NO_MORE_MEMORY 		-2001
#define CUBRID_ER_INVALID_SQL_TYPE 		-2002
#define CUBRID_ER_CANNOT_GET_COLUMN_INFO 	-2003
#define CUBRID_ER_INIT_ARRAY_FAIL 		-2004
#define CUBRID_ER_UNKNOWN_TYPE 			-2005
#define CUBRID_ER_INVALID_PARAM 		-2006
#define CUBRID_ER_INVALID_ARRAY_TYPE 		-2007
#define CUBRID_ER_NOT_SUPPORTED_TYPE 		-2008
#define CUBRID_ER_OPEN_FILE 			-2009
#define CUBRID_ER_CREATE_TEMP_FILE 		-2010
#define CUBRID_ER_TRANSFER_FAIL 		-2011
#define CUBRID_ER_PHP				-2012
#define CUBRID_ER_REMOVE_FILE 			-2013
#define CUBRID_ER_SQL_UNPREPARE                 -2014
#define CUBRID_ER_PARAM_UNBIND                  -2015
/* CAUTION! Also add the error message string to db_error[] */

/* Maximum length for the Cubrid data types */
#define MAX_CUBRID_CHAR_LEN   1073741823
#define MAX_LEN_INTEGER	      (10 + 1)
#define MAX_LEN_SMALLINT      (5 + 1)
#define MAX_LEN_BIGINT	      (19 + 1)
#define MAX_LEN_FLOAT	      (14 + 1)
#define MAX_LEN_DOUBLE	      (28 + 1)
#define MAX_LEN_MONETARY      (28 + 2)
#define MAX_LEN_DATE	      10
#define MAX_LEN_TIME	      8
#define MAX_LEN_TIMESTAMP     23
#define MAX_LEN_DATETIME      MAX_LEN_TIMESTAMP
#define MAX_LEN_OBJECT	      MAX_CUBRID_CHAR_LEN
#define MAX_LEN_SET	      MAX_CUBRID_CHAR_LEN
#define MAX_LEN_MULTISET      MAX_CUBRID_CHAR_LEN
#define MAX_LEN_SEQUENCE      MAX_CUBRID_CHAR_LEN

/* Max Cubrid supported charsets */
#define MAX_DB_CHARSETS 5

/* Max Cubrid unescaped string len */
#define MAX_UNESCAPED_STR_LEN 4096

/* Max number of auto increment columns in a class in cubrid_insert_id */
int MAX_AUTOINCREMENT_COLS = 16;

/* MAx length for column name in cubrid_insert_id */
int MAX_COLUMN_NAME_LEN = 256;

typedef struct
{
    int err_code;
    char *err_msg;
} DB_ERROR_INFO;

/* Define addtion error info */
static const DB_ERROR_INFO db_error[] = {
    {CUBRID_ER_NO_MORE_MEMORY, "Memory allocation error"},
    {CUBRID_ER_INVALID_SQL_TYPE, "Invalid API call"},
    {CUBRID_ER_CANNOT_GET_COLUMN_INFO, "annot get column info"},
    {CUBRID_ER_INIT_ARRAY_FAIL, "Array initializing error"},
    {CUBRID_ER_UNKNOWN_TYPE, "Unknown column type"},
    {CUBRID_ER_INVALID_PARAM, "Invalid parameter"},
    {CUBRID_ER_INVALID_ARRAY_TYPE, "Invalid array type"},
    {CUBRID_ER_NOT_SUPPORTED_TYPE, "Invalid type"},
    {CUBRID_ER_OPEN_FILE, "File open error"},
    {CUBRID_ER_CREATE_TEMP_FILE, "Temporary file open error"},
    {CUBRID_ER_TRANSFER_FAIL, "Glo transfering error"},
    {CUBRID_ER_PHP, "PHP error"},
    {CUBRID_ER_REMOVE_FILE, "Error removing file"},
    {CUBRID_ER_SQL_UNPREPARE, "SQL statement not prepared"},
    {CUBRID_ER_PARAM_UNBIND, "Some parameter not binded"}
};

typedef struct
{
    const char *charset_name;
    const char *charset_desc;
    const char *space_char;
    int charset_id;
    int default_collation;
    int space_size;
} DB_CHARSET;

/* Define Cubrid supported charsets, 
 * now we only use charset_name, so just set space_char to empty */
static const DB_CHARSET db_charsets[] = {
    {"ascii", "US English charset - ASCII encoding", "", 0, 0, 1},
    {"raw-bits", "Uninterpreted bits - Raw encoding", "", 1, 0, 1},
    {"raw-bytes", "Uninterpreted bytes - Raw encoding", "", 2, 0, 1},
    {"iso8859-1", "Latin 1 charset - ISO 8859 encoding", "", 3, 0, 1},
    {"ksc-euc", "KSC 5601 1990 charset - EUC encoding", "", 4, 0, 2},
    {"", "Unknown encoding", "", -1, 0, 0}
};

typedef struct
{
    char *type_name;
    T_CCI_U_TYPE cubrid_u_type;
    int len;
} DB_TYPE_INFO;

/* Define Cubrid supported date types */
static const DB_TYPE_INFO db_type_info[] = {
    {"NULL", CCI_U_TYPE_NULL, 0},
    {"UNKNOWN", CCI_U_TYPE_UNKNOWN, MAX_LEN_OBJECT},

    {"CHAR", CCI_U_TYPE_CHAR, -1},
    {"STRING", CCI_U_TYPE_STRING, -1},
    {"NCHAR", CCI_U_TYPE_NCHAR, -1},
    {"VARNCHAR", CCI_U_TYPE_VARNCHAR, -1},

    {"BIT", CCI_U_TYPE_BIT, -1},
    {"VARBIT", CCI_U_TYPE_VARBIT, -1},

    {"NUMERIC", CCI_U_TYPE_NUMERIC, -1},
    {"NUMBER", CCI_U_TYPE_NUMERIC, -1},
    {"INT", CCI_U_TYPE_INT, MAX_LEN_INTEGER},
    {"SHORT", CCI_U_TYPE_SHORT, MAX_LEN_SMALLINT},
    {"BIGINT", CCI_U_TYPE_BIGINT, MAX_LEN_BIGINT},
    {"MONETARY", CCI_U_TYPE_MONETARY, MAX_LEN_MONETARY},

    {"FLOAT", CCI_U_TYPE_FLOAT, MAX_LEN_FLOAT},
    {"DOUBLE", CCI_U_TYPE_DOUBLE, MAX_LEN_DOUBLE},

    {"DATE", CCI_U_TYPE_DATE, MAX_LEN_DATE},
    {"TIME", CCI_U_TYPE_TIME, MAX_LEN_TIME},
    {"DATETIME", CCI_U_TYPE_DATETIME, MAX_LEN_DATETIME},
    {"TIMESTAMP", CCI_U_TYPE_TIMESTAMP, MAX_LEN_TIMESTAMP},

    {"SET", CCI_U_TYPE_SET, MAX_LEN_SET},
    {"MULTISET", CCI_U_TYPE_MULTISET, MAX_LEN_MULTISET},
    {"SEQUENCE", CCI_U_TYPE_SEQUENCE, MAX_LEN_SEQUENCE},
    {"RESULTSET", CCI_U_TYPE_RESULTSET, -1},

    {"OBJECT", CCI_U_TYPE_OBJECT, MAX_LEN_OBJECT},
};

/* Define Cubrid DB parameters */
typedef struct
{
    T_CCI_DB_PARAM parameter_id;
    const char *parameter_name;
} DB_PARAMETER;

static const DB_PARAMETER db_parameters[] = {
    {CCI_PARAM_ISOLATION_LEVEL, "PARAM_ISOLATION_LEVEL"},
    {CCI_PARAM_LOCK_TIMEOUT, "LOCK_TIMEOUT"},
    {CCI_PARAM_MAX_STRING_LENGTH, "MAX_STRING_LENGTH"},
    {CCI_PARAM_AUTO_COMMIT, "PARAM_AUTO_COMMIT"}
};

/************************************************************************
* PRIVATE TYPE DEFINITIONS
************************************************************************/

typedef struct
{
    int handle;
} T_CUBRID_CONNECT;

typedef struct
{
    int handle;
    int affected_rows;
    int async_mode;
    int col_count;
    int row_count;
    int l_prepare;
    int bind_num;
    short *l_bind;
    int fetch_field_auto_index;
    T_CCI_CUBRID_STMT sql_type;
    T_CCI_COL_INFO *col_info;
} T_CUBRID_REQUEST;

/************************************************************************
* PRIVATE FUNCTION PROTOTYPES
************************************************************************/

static void php_cubrid_init_globals(zend_cubrid_globals *cubrid_globals);
static void close_cubrid_connect(T_CUBRID_CONNECT *conn);
static void close_cubrid_request(T_CUBRID_REQUEST *req);

static int init_error(void);
static int set_error(T_FACILITY_CODE facility, int code, char *msg, ...);
static int get_error_msg(int err_code, char *buf, int buf_size);
static int handle_error(int err_code, T_CCI_ERROR * error);

static T_CUBRID_REQUEST *new_cubrid_request(void);
static T_CUBRID_CONNECT *new_cubrid_connect(void);

static void php_cubrid_set_default_conn(int id TSRMLS_DC);
static void php_cubrid_set_default_req(int id TSRMLS_DC);

static int fetch_a_row(zval *arg, int req_handle, int type TSRMLS_DC);
static int type2str(T_CCI_COL_INFO *column_info, char *type_name, int type_name_len);
static long get_last_autoincrement(char *class_name, char **columns, char **values, int conn_handle);

static int cubrid_make_set(HashTable *ht, T_CCI_SET *set);
static int cubrid_add_index_array(zval *arg, uint index, T_CCI_SET in_set TSRMLS_DC);
static int cubrid_add_assoc_array(zval *arg, char *key, T_CCI_SET in_set TSRMLS_DC);
static int cubrid_array_destroy(HashTable *ht ZEND_FILE_LINE_DC);

static int numeric_type(T_CCI_U_TYPE type);
static int get_cubrid_u_type_by_name(const char *type_name);
static int get_cubrid_u_type_len(T_CCI_U_TYPE type);

/************************************************************************
* INTERFACE VARIABLES
************************************************************************/

char *cci_client_name = "PHP";

ZEND_BEGIN_ARG_INFO(arginfo_cubrid_version, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_connect, 0, 0, 3)
    ZEND_ARG_INFO(0, host)
    ZEND_ARG_INFO(0, port)
    ZEND_ARG_INFO(0, dbname)
    ZEND_ARG_INFO(0, userid)
    ZEND_ARG_INFO(0, passwd)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_connect_with_url, 0, 0, 1)
    ZEND_ARG_INFO(0, url)
    ZEND_ARG_INFO(0, userid)
    ZEND_ARG_INFO(0, passwd)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_disconnect, 0, 0, 1)
    ZEND_ARG_INFO(0, conn_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_prepare, 0, 0, 1)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, prepare_stmt)
    ZEND_ARG_INFO(0, option)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_bind, 0, 0, 3)
    ZEND_ARG_INFO(0, req_id)
    ZEND_ARG_INFO(0, bind_index)
    ZEND_ARG_INFO(0, bind_value)
    ZEND_ARG_INFO(0, bind_value_type)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_execute, 0, 0, 1)
    ZEND_ARG_INFO(0, id)
    ZEND_ARG_INFO(0, sql_stmt)
    ZEND_ARG_INFO(0, option)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_affected_rows, 0, 0, 1)
    ZEND_ARG_INFO(0, req_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_close_request, 0, 0, 1)
    ZEND_ARG_INFO(0, req_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_fetch, 0, 0, 1)
    ZEND_ARG_INFO(0, req_id)
    ZEND_ARG_INFO(0, type)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_current_oid, 0, 0, 1)
    ZEND_ARG_INFO(0, req_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_column_types, 0, 0, 1)
    ZEND_ARG_INFO(0, req_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_column_names, 0, 0, 1)
    ZEND_ARG_INFO(0, req_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_move_cursor, 0, 0, 2)
    ZEND_ARG_INFO(0, req_id)
    ZEND_ARG_INFO(0, offset)
    ZEND_ARG_INFO(0, origin)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_num_rows, 0, 0, 1)
    ZEND_ARG_INFO(0, req_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_num_cols, 0, 0, 1)
    ZEND_ARG_INFO(0, req_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_get, 0, 0, 2)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, oid)
    ZEND_ARG_INFO(0, attr)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_put, 0, 0, 3)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, oid)
    ZEND_ARG_INFO(0, attr)
    ZEND_ARG_INFO(0, value)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_drop, 0, 0, 2)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, oid)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_is_instance, 0, 0, 2)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, oid)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_lock_read, 0, 0, 2)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, oid)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_lock_write, 0, 0, 2)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, oid)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_get_class_name, 0, 0, 2)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, oid)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_schema, 0, 0, 2)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, schema_type)
    ZEND_ARG_INFO(0, class_name)
    ZEND_ARG_INFO(0, attr_name)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_col_size, 0, 0, 3)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, oid)
    ZEND_ARG_INFO(0, attr_name)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_col_get, 0, 0, 3)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, oid)
    ZEND_ARG_INFO(0, attr_name)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_set_add, 0, 0, 4)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, oid)
    ZEND_ARG_INFO(0, attr_name)
    ZEND_ARG_INFO(0, set_element)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_set_drop, 0, 0, 4)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, oid)
    ZEND_ARG_INFO(0, attr_name)
    ZEND_ARG_INFO(0, set_element)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_seq_insert, 0, 0, 5)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, oid)
    ZEND_ARG_INFO(0, attr_name)
    ZEND_ARG_INFO(0, index)
    ZEND_ARG_INFO(0, set_element)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_seq_put, 0, 0, 5)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, oid)
    ZEND_ARG_INFO(0, attr_name)
    ZEND_ARG_INFO(0, index)
    ZEND_ARG_INFO(0, set_element)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_seq_drop, 0, 0, 4)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, oid)
    ZEND_ARG_INFO(0, attr_name)
    ZEND_ARG_INFO(0, index)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_commit, 0, 0, 1)
    ZEND_ARG_INFO(0, conn_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_rollback, 0, 0, 1)
    ZEND_ARG_INFO(0, conn_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_new_glo, 0, 0, 3)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, class_name)
    ZEND_ARG_INFO(0, file_name)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_save_to_glo, 0, 0, 3)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, oid)
    ZEND_ARG_INFO(0, file_name)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_load_from_glo, 0, 0, 3)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, oid)
    ZEND_ARG_INFO(0, file_name)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_send_glo, 0, 0, 2)
    ZEND_ARG_INFO(0, conn_id)
    ZEND_ARG_INFO(0, oid)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO(arginfo_cubrid_error_msg, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO(arginfo_cubrid_error_code, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO(arginfo_cubrid_error_code_facility, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_field_name, 0, 0, 2)
    ZEND_ARG_INFO(0, req_id)
    ZEND_ARG_INFO(0, offset)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_field_table, 0, 0, 2)
    ZEND_ARG_INFO(0, req_id)
    ZEND_ARG_INFO(0, offset)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_field_type, 0, 0, 2)
    ZEND_ARG_INFO(0, req_id)
    ZEND_ARG_INFO(0, offset)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_field_flags, 0, 0, 2)
    ZEND_ARG_INFO(0, req_id)
    ZEND_ARG_INFO(0, offset)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_data_seek, 0, 0, 2)
    ZEND_ARG_INFO(0, req_id)
    ZEND_ARG_INFO(0, offset)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_fetch_array, 0, 0, 1)
    ZEND_ARG_INFO(0, req_id)
    ZEND_ARG_INFO(0, type)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_fetch_assoc, 0, 0, 1)
    ZEND_ARG_INFO(0, req_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_fetch_row, 0, 0, 1)
    ZEND_ARG_INFO(0, req_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_fetch_field, 0, 0, 1)
    ZEND_ARG_INFO(0, req_id)
    ZEND_ARG_INFO(0, offset)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_num_fields, 0, 0, 1)
    ZEND_ARG_INFO(0, req_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_free_result, 0, 0, 1)
    ZEND_ARG_INFO(0, req_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_fetch_lengths, 0, 0, 1)
    ZEND_ARG_INFO(0, req_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_fetch_object, 0, 0, 1)
    ZEND_ARG_INFO(0, req_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_field_seek, 0, 0, 1)
    ZEND_ARG_INFO(0, req_id)
    ZEND_ARG_INFO(0, offset)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_field_len, 0, 0, 2)
    ZEND_ARG_INFO(0, req_id)
    ZEND_ARG_INFO(0, offset)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_result, 0, 0, 2)
    ZEND_ARG_INFO(0, req_id)
    ZEND_ARG_INFO(0, row)
    ZEND_ARG_INFO(0, field)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_unbuffered_query, 0, 0, 1)
    ZEND_ARG_INFO(0, query)
    ZEND_ARG_INFO(0, conn_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_get_charset, 0, 0, 1)
    ZEND_ARG_INFO(0, conn_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO(arginfo_cubrid_get_client_info, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_get_server_info, 0, 0, 1)
    ZEND_ARG_INFO(0, conn_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_real_escape_string, 0, 0, 1)
    ZEND_ARG_INFO(0, unescaped_string)
    ZEND_ARG_INFO(0, conn_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_get_db_parameter, 0, 0, 1)
    ZEND_ARG_INFO(0, conn_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_list_dbs, 0, 0, 1)
    ZEND_ARG_INFO(0, conn_id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_cubrid_lnsert_id, 0, 0, 1)
    ZEND_ARG_INFO(0, class_name)
    ZEND_ARG_INFO(0, conn_id)
ZEND_END_ARG_INFO()

zend_function_entry cubrid_functions[] = {
    ZEND_FE(cubrid_version, arginfo_cubrid_version)
    ZEND_FE(cubrid_connect, arginfo_cubrid_connect)
    ZEND_FE(cubrid_connect_with_url, arginfo_cubrid_connect_with_url)
    ZEND_FE(cubrid_disconnect, arginfo_cubrid_disconnect)
    ZEND_FE(cubrid_prepare, arginfo_cubrid_prepare)
    ZEND_FE(cubrid_bind, arginfo_cubrid_bind)
    ZEND_FE(cubrid_execute, arginfo_cubrid_execute)
    ZEND_FE(cubrid_affected_rows, arginfo_cubrid_affected_rows)
    ZEND_FE(cubrid_close_request, arginfo_cubrid_close_request)
    ZEND_FE(cubrid_fetch, arginfo_cubrid_fetch)
    ZEND_FE(cubrid_current_oid, arginfo_cubrid_current_oid)
    ZEND_FE(cubrid_column_types, arginfo_cubrid_column_types)
    ZEND_FE(cubrid_column_names, arginfo_cubrid_column_names)
    ZEND_FE(cubrid_move_cursor, arginfo_cubrid_move_cursor)
    ZEND_FE(cubrid_num_rows, arginfo_cubrid_num_rows)
    ZEND_FE(cubrid_num_cols, arginfo_cubrid_num_cols)
    ZEND_FE(cubrid_get, arginfo_cubrid_get)
    ZEND_FE(cubrid_put, arginfo_cubrid_put)
    ZEND_FE(cubrid_drop, arginfo_cubrid_drop)
    ZEND_FE(cubrid_is_instance, arginfo_cubrid_is_instance)
    ZEND_FE(cubrid_lock_read, arginfo_cubrid_lock_read)
    ZEND_FE(cubrid_lock_write, arginfo_cubrid_lock_write)
    ZEND_FE(cubrid_get_class_name, arginfo_cubrid_get_class_name)
    ZEND_FE(cubrid_schema, arginfo_cubrid_schema)
    ZEND_FE(cubrid_col_size, arginfo_cubrid_col_size)
    ZEND_FE(cubrid_col_get, arginfo_cubrid_col_get)
    ZEND_FE(cubrid_set_add, arginfo_cubrid_set_add)
    ZEND_FE(cubrid_set_drop, arginfo_cubrid_set_drop)
    ZEND_FE(cubrid_seq_insert, arginfo_cubrid_seq_insert)
    ZEND_FE(cubrid_seq_put, arginfo_cubrid_seq_put)
    ZEND_FE(cubrid_seq_drop, arginfo_cubrid_seq_drop)
    ZEND_FE(cubrid_commit, arginfo_cubrid_commit)
    ZEND_FE(cubrid_rollback, arginfo_cubrid_rollback)
    ZEND_FE(cubrid_new_glo, arginfo_cubrid_new_glo)
    ZEND_FE(cubrid_save_to_glo, arginfo_cubrid_save_to_glo)
    ZEND_FE(cubrid_load_from_glo, arginfo_cubrid_load_from_glo)
    ZEND_FE(cubrid_send_glo, arginfo_cubrid_send_glo)
    ZEND_FE(cubrid_error_msg, arginfo_cubrid_error_msg)
    ZEND_FE(cubrid_error_code, arginfo_cubrid_error_code)
    ZEND_FE(cubrid_error_code_facility, arginfo_cubrid_error_code_facility)
    ZEND_FE(cubrid_field_name, arginfo_cubrid_field_name)
    ZEND_FE(cubrid_field_table, arginfo_cubrid_field_table)
    ZEND_FE(cubrid_field_type, arginfo_cubrid_field_type)
    ZEND_FE(cubrid_field_flags, arginfo_cubrid_field_flags)
    ZEND_FE(cubrid_data_seek, arginfo_cubrid_data_seek)
    ZEND_FE(cubrid_fetch_array, arginfo_cubrid_fetch_array)
    ZEND_FE(cubrid_fetch_assoc, arginfo_cubrid_fetch_assoc)
    ZEND_FE(cubrid_fetch_row, arginfo_cubrid_fetch_row)
    ZEND_FE(cubrid_fetch_field, arginfo_cubrid_fetch_field)
    ZEND_FE(cubrid_num_fields, arginfo_cubrid_num_fields)
    ZEND_FE(cubrid_free_result, arginfo_cubrid_free_result)
    ZEND_FE(cubrid_fetch_lengths, arginfo_cubrid_fetch_lengths)
    ZEND_FE(cubrid_fetch_object, arginfo_cubrid_fetch_object)
    ZEND_FE(cubrid_field_seek, arginfo_cubrid_field_seek)
    ZEND_FE(cubrid_field_len, arginfo_cubrid_field_len)
    ZEND_FE(cubrid_result, arginfo_cubrid_result)
    ZEND_FE(cubrid_get_charset, arginfo_cubrid_get_charset)
    ZEND_FE(cubrid_unbuffered_query, arginfo_cubrid_unbuffered_query)
    ZEND_FE(cubrid_get_client_info, arginfo_cubrid_get_client_info)
    ZEND_FE(cubrid_get_server_info, arginfo_cubrid_get_server_info)
    ZEND_FE(cubrid_real_escape_string, arginfo_cubrid_real_escape_string)
    ZEND_FE(cubrid_get_db_parameter, arginfo_cubrid_get_db_parameter)
    ZEND_FE(cubrid_list_dbs, arginfo_cubrid_list_dbs)
    ZEND_FE(cubrid_insert_id, arginfo_cubrid_lnsert_id)
    ZEND_FALIAS(cubrid_close_prepare, cubrid_close_request, NULL) 
    {NULL, NULL, NULL}
};

zend_module_entry cubrid_module_entry = {
    STANDARD_MODULE_HEADER,
    "CUBRID",
    cubrid_functions,
    ZEND_MINIT(cubrid),
    ZEND_MSHUTDOWN(cubrid),
    NULL,
    NULL,
    ZEND_MINFO(cubrid),
    NO_VERSION_YET,
    STANDARD_MODULE_PROPERTIES
};

ZEND_DECLARE_MODULE_GLOBALS(cubrid)

/************************************************************************
* CUBRID PHP.INI SETTINGS
************************************************************************/

ZEND_INI_BEGIN()
/* maybe add settings later */
ZEND_INI_END()

/************************************************************************
* PRIVATE VARIABLES
************************************************************************/

/* resource type */
static int le_connect, le_request;

/************************************************************************
* IMPLEMENTATION OF CALLBACK FUNCTION (EXPORT/INIT/SHUTDOWN/INFO)
************************************************************************/

#if defined(COMPILE_DL_CUBRID)
ZEND_GET_MODULE(cubrid)
#endif

ZEND_MINIT_FUNCTION(cubrid)
{
    REGISTER_INI_ENTRIES();

    cci_init();

    ZEND_INIT_MODULE_GLOBALS(cubrid, php_cubrid_init_globals, NULL);

    le_connect = register_list_destructors(close_cubrid_connect, NULL);
    le_request = register_list_destructors(close_cubrid_request, NULL);

    Z_TYPE(cubrid_module_entry) = type;

    init_error();

    REGISTER_LONG_CONSTANT("CUBRID_INCLUDE_OID", CUBRID_INCLUDE_OID, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_ASYNC", CUBRID_ASYNC, CONST_CS | CONST_PERSISTENT);

    REGISTER_LONG_CONSTANT("CUBRID_NUM", CUBRID_NUM, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_ASSOC", CUBRID_ASSOC, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_BOTH", CUBRID_BOTH, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_OBJECT", CUBRID_OBJECT, CONST_CS | CONST_PERSISTENT);

    REGISTER_LONG_CONSTANT("CUBRID_CURSOR_FIRST", CUBRID_CURSOR_FIRST, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_CURSOR_CURRENT", CUBRID_CURSOR_CURRENT, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_CURSOR_LAST", CUBRID_CURSOR_LAST, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_CURSOR_SUCCESS", CUBRID_CURSOR_SUCCESS, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_NO_MORE_DATA", CUBRID_NO_MORE_DATA, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_CURSOR_ERROR", CUBRID_CURSOR_ERROR, CONST_CS | CONST_PERSISTENT);

    REGISTER_LONG_CONSTANT("CUBRID_SCH_CLASS", CUBRID_SCH_CLASS, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_SCH_VCLASS", CUBRID_SCH_VCLASS, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_SCH_QUERY_SPEC", CUBRID_SCH_QUERY_SPEC, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_SCH_ATTRIBUTE", CUBRID_SCH_ATTRIBUTE, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_SCH_CLASS_ATTRIBUTE", CUBRID_SCH_CLASS_ATTRIBUTE, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_SCH_METHOD", CUBRID_SCH_METHOD, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_SCH_CLASS_METHOD", CUBRID_SCH_CLASS_METHOD, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_SCH_METHOD_FILE", CUBRID_SCH_METHOD_FILE, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_SCH_SUPERCLASS", CUBRID_SCH_SUPERCLASS, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_SCH_SUBCLASS", CUBRID_SCH_SUBCLASS, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_SCH_CONSTRAINT", CUBRID_SCH_CONSTRAINT, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_SCH_TRIGGER", CUBRID_SCH_TRIGGER, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_SCH_CLASS_PRIVILEGE", CUBRID_SCH_CLASS_PRIVILEGE, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_SCH_ATTR_PRIVILEGE", CUBRID_SCH_ATTR_PRIVILEGE, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_SCH_DIRECT_SUPER_CLASS", CUBRID_SCH_DIRECT_SUPER_CLASS, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_SCH_PRIMARY_KEY", CUBRID_SCH_PRIMARY_KEY, CONST_CS | CONST_PERSISTENT);

    REGISTER_LONG_CONSTANT("CUBRID_FACILITY_DBMS", CUBRID_FACILITY_DBMS, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_FACILITY_CAS", CUBRID_FACILITY_CAS, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_FACILITY_CCI", CUBRID_FACILITY_CCI, CONST_CS | CONST_PERSISTENT);
    REGISTER_LONG_CONSTANT("CUBRID_FACILITY_CLIENT", CUBRID_FACILITY_CLIENT, CONST_CS | CONST_PERSISTENT);

    return SUCCESS;
}

ZEND_MSHUTDOWN_FUNCTION(cubrid)
{
    UNREGISTER_INI_ENTRIES();

    cci_end();

    return SUCCESS;
}

ZEND_MINFO_FUNCTION(cubrid)
{
    php_info_print_table_start();
    php_info_print_table_header(2, "CUBRID support", "enabled");
    php_info_print_table_row(2, "Client API version", PHP_CUBRID_VERSION);
    php_info_print_table_row(2, "Supported CUBRID server", "8.3.0");
    php_info_print_table_end();

    DISPLAY_INI_ENTRIES();
}

/************************************************************************
* IMPLEMENTATION OF CUBRID API
************************************************************************/

ZEND_FUNCTION(cubrid_version)
{
    if (zend_parse_parameters_none() == FAILURE) {
	return;
    }

    RETURN_STRINGL(PHP_CUBRID_VERSION, strlen(PHP_CUBRID_VERSION), 1);
}

ZEND_FUNCTION(cubrid_connect)
{
    char *host = NULL, *dbname = NULL, *userid = NULL, *passwd = NULL;
    long port = 0;
    int host_len, dbname_len, userid_len, passwd_len;

    int cubrid_conn, cubrid_retval = 0;
    int isolation_level;

    T_CUBRID_CONNECT *connect = NULL;
    T_CCI_ERROR error;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "sls|ss", 
		&host, &host_len, &port, &dbname, &dbname_len, 
		&userid, &userid_len, &passwd, &passwd_len) == FAILURE) {
	return;
    }

    if (!userid) {
	userid = CUBRID_G(default_userid);
    }

    if (!passwd) {
	passwd = CUBRID_G(default_passwd);
    }

    if ((cubrid_conn = cci_connect(host, port, dbname, userid, passwd)) < 0) {
	handle_error(cubrid_conn, NULL);
	RETURN_FALSE;
    }

    CUBRID_G(last_request_id) = -1;
    CUBRID_G(last_request_stmt_type) = 0;
    CUBRID_G(last_request_affected_rows) = 0;

    if ((cubrid_retval = cci_get_db_parameter(cubrid_conn, CCI_PARAM_ISOLATION_LEVEL, 
		    &isolation_level, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	cci_disconnect(cubrid_conn, &error);
	RETURN_FALSE;
    }

    if ((cubrid_retval = cci_end_tran(cubrid_conn, CCI_TRAN_COMMIT, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	cci_disconnect(cubrid_conn, &error);
	RETURN_FALSE;
    }

    connect = new_cubrid_connect();
    connect->handle = cubrid_conn;

    ZEND_REGISTER_RESOURCE(return_value, connect, le_connect);
    php_cubrid_set_default_conn(Z_LVAL_P(return_value) TSRMLS_CC);
}

ZEND_FUNCTION(cubrid_connect_with_url)
{
    char *url = NULL, *userid = NULL, *passwd = NULL;
    int url_len, userid_len, passwd_len;

    int cubrid_conn, cubrid_retval = 0;
    int isolation_level;

    T_CUBRID_CONNECT *connect = NULL;
    T_CCI_ERROR error;

    init_error();

    if (zend_parse_parameters (ZEND_NUM_ARGS() TSRMLS_CC, "s|ss", 
		&url, &url_len, &userid, &userid_len, &passwd, &passwd_len) == FAILURE) {
	return;
    }

    if (!userid) {
	userid = CUBRID_G(default_userid);
    }

    if (!passwd) {
	passwd = CUBRID_G(default_passwd);
    }

    if ((cubrid_conn = cci_connect_with_url(url, userid, passwd)) < 0) {
	handle_error(cubrid_conn, NULL);
	RETURN_FALSE;
    }

    CUBRID_G(last_request_id) = -1;
    CUBRID_G(last_request_stmt_type) = 0;
    CUBRID_G(last_request_affected_rows) = 0;

    if ((cubrid_retval = cci_get_db_parameter(cubrid_conn, 
		    CCI_PARAM_ISOLATION_LEVEL, &isolation_level, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	cci_disconnect(cubrid_conn, &error);
	RETURN_FALSE;
    }

    if ((cubrid_retval = cci_end_tran(cubrid_conn, CCI_TRAN_COMMIT, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	cci_disconnect(cubrid_conn, &error);
	RETURN_FALSE;
    }

    connect = new_cubrid_connect();
    connect->handle = cubrid_conn;

    ZEND_REGISTER_RESOURCE(return_value, connect, le_connect);
    php_cubrid_set_default_conn(Z_LVAL_P(return_value) TSRMLS_CC);
}

ZEND_FUNCTION(cubrid_disconnect)
{
    zval *conn_id = NULL;
    T_CUBRID_CONNECT *connect;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &conn_id) == FAILURE) {
	return;
    }

    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);
    zend_list_delete(Z_RESVAL_P(conn_id));

    /* On an explicit close of the default connection it had a refcount of 2,
     * so we need one more call */
    if (Z_RESVAL_P(conn_id) == CUBRID_G(last_connect_id)) {
        CUBRID_G(last_connect_id) = -1;
        CUBRID_G(last_request_id) = -1;
        CUBRID_G(last_request_stmt_type) = 0;
        CUBRID_G(last_request_affected_rows) = 0;

        zend_list_delete(Z_RESVAL_P(conn_id));
    }

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_prepare)
{
    zval * conn_id = NULL;
    char *query = NULL;
    long option = 0;
    int query_len;

    T_CUBRID_CONNECT *connect = NULL;
    T_CUBRID_REQUEST *request = NULL;
    T_CCI_ERROR error;

    int cubrid_retval = 0, request_handle = -1;
    int i;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rs|l", 
		&conn_id, &query, &query_len, &option) == FAILURE) {
	return;
    }

    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if ((cubrid_retval = cci_prepare(connect->handle, query, 
		    (char) ((option & CUBRID_INCLUDE_OID) ? CCI_PREPARE_INCLUDE_OID : 0), &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }
    request_handle = cubrid_retval;

    request = new_cubrid_request();
    request->handle = request_handle;
    request->bind_num = cci_get_bind_num(request_handle);

    if (request->bind_num > 0) {
	request->l_bind = (short *) safe_emalloc(request->bind_num, sizeof(short), 0);
	for (i = 0; i < request->bind_num; i++) {
	    request->l_bind[i] = 0;
	}
    }

    request->l_prepare = 1;
    request->fetch_field_auto_index = 0;

    ZEND_REGISTER_RESOURCE(return_value, request, le_request);
    php_cubrid_set_default_req(Z_LVAL_P(return_value) TSRMLS_CC);
}

ZEND_FUNCTION(cubrid_bind)
{
    zval *req_id = NULL;
    char *bind_value = NULL, *bind_value_type = NULL;
    long bind_index = -1;
    int bind_value_len, bind_value_type_len;

    T_CUBRID_REQUEST *request = NULL;
    T_CCI_U_TYPE u_type = -1;
    T_CCI_BIT *bit_value = NULL;

    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters (ZEND_NUM_ARGS() TSRMLS_CC, "rls|s", 
		&req_id, &bind_index, &bind_value, &bind_value_len, 
		&bind_value_type, &bind_value_type_len) == FAILURE) {
	return;
    }

    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    if (bind_index < 1 || bind_index > request->bind_num) {
	RETURN_FALSE;
    }

    if (!bind_value_type) {
	u_type = CCI_U_TYPE_STRING;
    } else {
	u_type = get_cubrid_u_type_by_name(bind_value_type);
	/* collection type should be made by cci_set_make before calling cci_bind_param */
	if (u_type == CCI_U_TYPE_UNKNOWN || u_type == CCI_U_TYPE_SET || 
	    u_type == CCI_U_TYPE_MULTISET || u_type == CCI_U_TYPE_SEQUENCE) {
	    php_error_docref(NULL TSRMLS_CC, E_WARNING, "Bind value type unknown : %s\n", bind_value_type);
	    RETURN_FALSE;
	}
    }

    if (u_type == CCI_U_TYPE_NULL || bind_value == NULL) {
        cubrid_retval = cci_bind_param(request->handle, bind_index, CCI_A_TYPE_STR, NULL, u_type, 0);
    } else {
        if (u_type == CCI_U_TYPE_BIT) {
            bit_value = (T_CCI_BIT *) emalloc(sizeof(T_CCI_BIT));
            bit_value->size = bind_value_len;
            bit_value->buf = bind_value;

            cubrid_retval = cci_bind_param(request->handle, bind_index, 
                    CCI_A_TYPE_BIT, (void *) bit_value, CCI_U_TYPE_BIT, 0);

            efree(bit_value);
        } else {
            if (u_type == CCI_U_TYPE_NULL) {
                bind_value = NULL;
            }

            cubrid_retval = cci_bind_param(request->handle, bind_index, 
                    CCI_A_TYPE_STR, bind_value, u_type, 0);
        }
    }

    if (cubrid_retval != 0 || !request->l_bind) {
	handle_error(cubrid_retval, NULL);
	RETURN_FALSE;
    }

    request->l_bind[bind_index - 1] = 1;

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_execute)
{
    zval *id = NULL, *param = NULL;
    char *sql_stmt = NULL;
    long option = 0;
    int sql_stmt_len;

    T_CUBRID_CONNECT *connect = NULL;
    T_CUBRID_REQUEST *request = NULL;
    T_CCI_ERROR error;

    T_CCI_COL_INFO *res_col_info;
    T_CCI_CUBRID_STMT res_sql_type;
    int res_col_count = 0;

    int cubrid_retval = 0;
    int req_handle = 0;
    int l_prepare = 0;
    int i;

    init_error();

    switch (ZEND_NUM_ARGS()) {
    case 1:
	/* It must be req_id */
	if (zend_parse_parameters(1 TSRMLS_CC, "r", &id) == FAILURE) {
	    return;
	}

	ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &id, -1, "CUBRID-Request", le_request);

	break;
    case 2:
	/* Param may be conn_id + sql_stmt or req_id + option */
	if (zend_parse_parameters(2 TSRMLS_CC, "rz", &id, &param) == FAILURE) {
	    return;
	}

	switch (Z_TYPE_P(param)) {
	case IS_STRING:
	    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &id, -1, "CUBRID-Connect", le_connect);
	    sql_stmt = Z_STRVAL_P(param);	
	    sql_stmt_len = Z_STRLEN_P(param);

	    break;
	case IS_LONG:
	    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &id, -1, "CUBRID-Request", le_request);
	    option = Z_LVAL_P(param);	

	    break;
	default:
	    RETURN_FALSE;
	}

	break;
    case 3:
	/* It must be conn_id + sql_stmt + option */
	if (zend_parse_parameters(3 TSRMLS_CC, "rsl", &id, &sql_stmt, &sql_stmt_len, &option) == FAILURE) {
	    return;
	}

	ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &id, -1, "CUBRID-Connect", le_connect);

	break;
    default:
	WRONG_PARAM_COUNT;
    }

    if (!connect) {
	/* req_id */
	if (!request) {
	    RETURN_FALSE;
	}

	if (!request->l_prepare) {
            handle_error(CUBRID_ER_PARAM_UNBIND, NULL);
	    RETURN_FALSE;
	}

	l_prepare = request->l_prepare;

	if (request->bind_num > 0) {
	    if (!request->l_bind) {
                handle_error(CUBRID_ER_PARAM_UNBIND, NULL);
		RETURN_FALSE;
	    }

	    for (i = 0; i < request->bind_num; i++) {
		if (!request->l_bind[i]) {
                    handle_error(CUBRID_ER_PARAM_UNBIND, NULL);
		    RETURN_FALSE;
		}
	    }
	}

	req_handle = request->handle;
    } else {
	/* conn_id */
	if ((cubrid_retval = cci_prepare(connect->handle, sql_stmt, 
			(char) ((option & CUBRID_INCLUDE_OID) ? CCI_PREPARE_INCLUDE_OID : 0), &error)) < 0) {
	    handle_error(cubrid_retval, &error);
	    RETURN_FALSE;
	}

	req_handle = cubrid_retval;
    }

    if ((cubrid_retval = cci_execute(req_handle, 
		    (char) ((option & CUBRID_ASYNC) ? CCI_EXEC_ASYNC : 0), 0, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    res_col_info = cci_get_result_info(req_handle, &res_sql_type, &res_col_count);
    if (res_sql_type == CUBRID_STMT_SELECT && !res_col_info) {
	RETURN_FALSE;
    }

    if (!l_prepare) {
	request = new_cubrid_request();
    }

    request->handle = req_handle;
    request->async_mode = option & CUBRID_ASYNC;

    request->col_info = res_col_info;
    request->sql_type = res_sql_type;
    request->col_count = res_col_count;

    switch (request->sql_type) {
    case CUBRID_STMT_SELECT:
	request->row_count = cubrid_retval;
	break;
    case CUBRID_STMT_INSERT:
    case CUBRID_STMT_UPDATE:
    case CUBRID_STMT_DELETE:
	request->affected_rows = cubrid_retval;
	break;
    case CUBRID_STMT_CALL:
	request->row_count = cubrid_retval;

    default:
	break;
    }

    cubrid_retval = cci_cursor(req_handle, 1, CCI_CURSOR_CURRENT, &error);
    if (cubrid_retval < 0 && cubrid_retval != CCI_ER_NO_MORE_DATA) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    request->fetch_field_auto_index = 0;

    CUBRID_G(last_request_stmt_type) = request->sql_type;
    CUBRID_G(last_request_affected_rows) = request->affected_rows;

    if (l_prepare) {
	if (request->l_bind) {
	    for (i = 0; i < request->bind_num; i++) {
		request->l_bind[i] = 0;
	    }
	}
    } else {
	ZEND_REGISTER_RESOURCE(return_value, request, le_request);
        php_cubrid_set_default_req(Z_LVAL_P(return_value) TSRMLS_CC);
	return;
    }

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_affected_rows)
{
    zval *req_id = NULL;
    T_CUBRID_REQUEST *request;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &req_id) == FAILURE) {
	return;
    }

    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    switch (request->sql_type) {
    case CUBRID_STMT_INSERT:
    case CUBRID_STMT_UPDATE:
    case CUBRID_STMT_DELETE:
	RETURN_LONG(request->affected_rows);
    default:
	handle_error(CUBRID_ER_INVALID_SQL_TYPE, NULL);
	RETURN_LONG(-1);
    }
}

ZEND_FUNCTION(cubrid_close_request)
{
    zval *req_id = NULL;
    T_CUBRID_REQUEST *request;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &req_id) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);
    zend_list_delete(Z_RESVAL_P(req_id));

    /* On an explicit close of the default request it had a refcount of 2,
     * so we need one more call */
    if (Z_RESVAL_P(req_id) == CUBRID_G(last_request_id)) {
        CUBRID_G(last_request_id) = -1;
        CUBRID_G(last_request_stmt_type) = 0;
        CUBRID_G(last_request_affected_rows) = 0;

        zend_list_delete(Z_RESVAL_P(req_id));
    }

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_fetch)
{
    zval *req_id = NULL;
    long type = CUBRID_BOTH;

    T_CUBRID_REQUEST *request;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r|l", &req_id, &type) == FAILURE) {
	return;
    }

    if (type & CUBRID_OBJECT) {
	type |= CUBRID_ASSOC;
    }

    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    cubrid_retval = cci_cursor(request->handle, 0, CCI_CURSOR_CURRENT, &error);
    if (cubrid_retval == CCI_ER_NO_MORE_DATA) {
	RETURN_FALSE;
    }

    cubrid_retval = cci_fetch(request->handle, &error);
    if (cubrid_retval < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    if ((cubrid_retval = fetch_a_row(return_value, request->handle, type TSRMLS_CC))
	!= SUCCESS) {
	handle_error(cubrid_retval, NULL);
	RETURN_FALSE;
    }

    if (type & CUBRID_OBJECT) {
	if (return_value->type == IS_ARRAY) {
	    convert_to_object(return_value);
	}
    }

    cubrid_retval = cci_cursor(request->handle, 1, CCI_CURSOR_CURRENT, &error);
    if (cubrid_retval < 0 && cubrid_retval != CCI_ER_NO_MORE_DATA) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    return;
}

ZEND_FUNCTION(cubrid_current_oid)
{
    zval *req_id = NULL;
    T_CUBRID_REQUEST *request;

    char oid_buf[1024];
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &req_id) == FAILURE) {
	return;
    }

    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    if (request->sql_type != CUBRID_STMT_SELECT) {
	handle_error(CUBRID_ER_INVALID_SQL_TYPE, NULL);
	RETURN_FALSE;
    }

    if ((cubrid_retval = cci_get_cur_oid(request->handle, oid_buf)) < 0) {
	handle_error(cubrid_retval, NULL);
	RETURN_FALSE;
    }

    RETURN_STRING(oid_buf, 1);
}

ZEND_FUNCTION(cubrid_column_types)
{
    zval *req_id = NULL;
    T_CUBRID_REQUEST *request;

    char full_type_name[128];
    int i;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &req_id) == FAILURE) {
	return;
    }

    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);
    
    array_init(return_value);

    for (i = 0; i < request->col_count; i++) {
	if (type2str(&request->col_info[i], full_type_name, sizeof(full_type_name)) < 0) {
	    handle_error(CUBRID_ER_UNKNOWN_TYPE, NULL);
            cubrid_array_destroy(return_value->value.ht ZEND_FILE_LINE_CC);
	    RETURN_FALSE;
	}

	add_index_stringl(return_value, i, full_type_name, strlen(full_type_name), 1);
    }

    return;
}

ZEND_FUNCTION(cubrid_column_names)
{
    zval *req_id = NULL;
    T_CUBRID_REQUEST *request;
    char *column_name;

    int i;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &req_id) == FAILURE) {
	return;
    }

    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);
    
    array_init(return_value);

    for (i = 0; i < request->col_count; i++) {
	column_name = CCI_GET_RESULT_INFO_NAME(request->col_info, i + 1);
	add_index_stringl(return_value, i, column_name, strlen(column_name), 1);
    }

    return;
}

ZEND_FUNCTION(cubrid_move_cursor)
{
    zval *req_id = NULL;
    long offset = 0, origin = CUBRID_CURSOR_CURRENT;

    T_CUBRID_REQUEST *request = NULL;

    T_CCI_ERROR error;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rl|l", 
		&req_id, &offset, &origin) == FAILURE) {
	return;
    }

    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    cubrid_retval = cci_cursor(request->handle, offset, origin, &error);
    if (cubrid_retval == CCI_ER_NO_MORE_DATA) {
	RETURN_LONG(CUBRID_NO_MORE_DATA);
    }

    if (cubrid_retval < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_LONG(CUBRID_CURSOR_ERROR);
    }

    RETURN_LONG(CUBRID_CURSOR_SUCCESS);
}

ZEND_FUNCTION(cubrid_num_rows)
{
    zval *req_id = NULL;
    T_CUBRID_REQUEST *request = NULL;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &req_id) == FAILURE) {
	return;
    }

    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    switch (request->sql_type) {
    case CUBRID_STMT_SELECT:
	RETURN_LONG(request->row_count);
    default:
	handle_error(CUBRID_ER_INVALID_SQL_TYPE, NULL);
	RETURN_LONG(-1);
    }
}

ZEND_FUNCTION(cubrid_num_cols)
{
    zval *req_id = NULL;
    T_CUBRID_REQUEST *request;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &req_id) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);
    
    switch (request->sql_type) {
    case CUBRID_STMT_SELECT:
	RETURN_LONG(request->col_count);
    default:
	handle_error(CUBRID_ER_INVALID_SQL_TYPE, NULL);
	RETURN_LONG(-1);
    }
}

ZEND_FUNCTION(cubrid_get)
{
    zval *conn_id= NULL, *attr_name = NULL;
    char *oid = NULL;
    int oid_len, attr_count = -1;

    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;
    int cubrid_retval = 0;
    int i;

    zval **elem_buf = NULL;
    char **attr = NULL;
    int request_handle;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rs|z", 
		&conn_id, &oid, &oid_len, &attr_name) == FAILURE) {
	return;
    }

    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if (attr_name) {
	switch (Z_TYPE_P(attr_name)) {
	case IS_STRING:
	    attr_count = 1;
	    attr = (char **) safe_emalloc(attr_count + 1, sizeof(char *), 0);

	    convert_to_string_ex(&attr_name);

	    attr[0] = estrndup(Z_STRVAL_P(attr_name), Z_STRLEN_P(attr_name));
	    attr[1] = NULL;

	    break;
	case IS_ARRAY:
	    attr_count = zend_hash_num_elements(HASH_OF(attr_name));
	    attr = (char **) safe_emalloc(attr_count + 1, sizeof(char *), 0);

	    for (i = 0; i <= attr_count; i++) {
		attr[i] = NULL;
	    }

	    for (i = 0; i < attr_count; i++) {
		if (zend_hash_index_find(HASH_OF(attr_name), i, (void **) &elem_buf) == FAILURE) {
		    handle_error(CUBRID_ER_INVALID_PARAM, NULL);
		    goto ERR_CUBRID_GET;
		}
		convert_to_string_ex(elem_buf);
		attr[i] = estrdup(Z_STRVAL_P(*elem_buf));
	    }
	    attr[i] = NULL;

	    break;
	default:
	    handle_error(CUBRID_ER_INVALID_PARAM, NULL);
	    RETURN_FALSE;
	}
    }

    cubrid_retval = cci_oid_get(connect->handle, oid, attr, &error);
    if (cubrid_retval < 0) {
	handle_error(cubrid_retval, &error);
	goto ERR_CUBRID_GET;
    }
    request_handle = cubrid_retval;

    /* free memory before return */
    for (i = 0; i < attr_count; i++) {
 	if (attr[i]) {
	    efree(attr[i]);   
	}
    }

    if (attr) {
	efree(attr);
    }

    if (attr_name && Z_TYPE_P(attr_name) == IS_STRING) {
	char *result;
	int ind;

	cubrid_retval = cci_get_data(request_handle, 1, CCI_A_TYPE_STR, &result, &ind);
	if (cubrid_retval < 0) {
	    handle_error(cubrid_retval, &error);
	    RETURN_FALSE;
	}

	if (ind < 0) {
	    RETURN_FALSE;
	} else {
	    RETURN_STRINGL(result, ind, 1);
	}
    } else {
	if ((cubrid_retval = fetch_a_row(return_value, request_handle, CUBRID_ASSOC TSRMLS_CC)) != SUCCESS) {
	    handle_error(cubrid_retval, NULL);
	    RETURN_FALSE;
	}
    }

    return;

ERR_CUBRID_GET:

    for (i = 0; i < attr_count; i++) {
 	if (attr[i]) {
	    efree(attr[i]);   
	}
    }

    if (attr) {
	efree(attr);
    }
    
    RETURN_FALSE;
}

ZEND_FUNCTION(cubrid_put)
{
    zval *conn_id = NULL, *attr_value = NULL;
    char *oid = NULL, *attr = NULL;
    int oid_len, attr_len;

    char **attr_name = NULL;
    int attr_count = 0;
    int *attr_type = NULL;

    T_CUBRID_CONNECT *connect;
    T_CCI_SET temp_set = NULL;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    void **value = NULL;
    char *key = NULL;
    ulong index;
    zval **data = NULL;
    int i;

    init_error();

    switch (ZEND_NUM_ARGS()) {
    case 3:
	if (zend_parse_parameters(3 TSRMLS_CC, "rsa", 
		    &conn_id, &oid, &oid_len, &attr_value) == FAILURE) {
	    return;
	}

	ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

	attr_count = zend_hash_num_elements(HASH_OF(attr_value));
	zend_hash_internal_pointer_reset(HASH_OF(attr_value));

	attr_name = (char **) safe_emalloc(attr_count + 1, sizeof(char *), 0);
	value = safe_emalloc(attr_count + 1, sizeof(char *), 0);
	attr_type = (int *) safe_emalloc(attr_count + 1, sizeof(int), 0);

	if (attr_count > 0) {
	    for (i = 0; i < attr_count; i++) {
		if (zend_hash_get_current_key(HASH_OF(attr_value), &key, &index, 1) == HASH_KEY_NON_EXISTANT) {
		    break;
		}

		if (cubrid_retval == HASH_KEY_IS_LONG) {
		    handle_error(CUBRID_ER_INVALID_ARRAY_TYPE, NULL);
		    RETURN_FALSE;
		}

		attr_name[i] = (char *) safe_emalloc(strlen(key) + 1, sizeof(char), 0);
		strlcpy(attr_name[i], key, strlen(key) + 1);
		value[i] = NULL;
		attr_type[i] = 0;

		efree(key);

		zend_hash_get_current_data(HASH_OF(attr_value), (void **) &data);
		switch (Z_TYPE_PP(data)) {
		case IS_NULL:
		    value[i] = NULL;

		    break;
		case IS_LONG:
		case IS_DOUBLE:
		    convert_to_string_ex(data);
		case IS_STRING:
		    value[i] = (char *) safe_emalloc(Z_STRLEN_PP(data) + 1, sizeof(char), 0);
		    strlcpy(value[i], Z_STRVAL_PP(data), Z_STRLEN_PP(data) + 1);
		    attr_type[i] = CCI_A_TYPE_STR;

		    break;
		case IS_ARRAY:
		    cubrid_retval = cubrid_make_set(HASH_OF(*data), &temp_set);
		    if (cubrid_retval < 0) {
			handle_error(cubrid_retval, NULL);
			goto ERR_CUBRID_PUT;
		    }

		    value[i] = temp_set;
		    attr_type[i] = CCI_A_TYPE_SET;

		    break;
		case IS_OBJECT:
		case IS_BOOL:
		case IS_RESOURCE:
		case IS_CONSTANT:
		    cubrid_retval = -1;
		    handle_error(CUBRID_ER_NOT_SUPPORTED_TYPE, NULL);
		    goto ERR_CUBRID_PUT;
		}

		zend_hash_move_forward(HASH_OF(attr_value));
	    }

	    attr_name[attr_count] = NULL;
	    value[attr_count] = NULL;	
	}

	break;
    case 4:
	if (zend_parse_parameters(4 TSRMLS_CC, "rssz", 
		    &conn_id, &oid, &oid_len, &attr, &attr_len, &attr_value) == FAILURE) {
	    return;
	}

	ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

	attr_count = 1;

	attr_name = (char **) safe_emalloc(attr_count + 1, sizeof(char *), 0);
	value = safe_emalloc(attr_count + 1, sizeof(char *), 0);
	attr_type = safe_emalloc(attr_count + 1, sizeof(int), 0);

	attr_name[0] = (char *) safe_emalloc (attr_len + 1, sizeof(char), 0);
	strlcpy(attr_name[0], attr, attr_len + 1);

	attr_name[1] = NULL;

	value[0] = NULL;
	attr_type[0] = 0;

	switch (Z_TYPE_P(attr_value)) {
	case IS_NULL:
	    value[0] = NULL;
	    
	    break;
	case IS_LONG:
	case IS_DOUBLE:
	    convert_to_string_ex(&attr_value);
	case IS_STRING:
	    value[0] = (char *) safe_emalloc(Z_STRLEN_P(attr_value) + 1, sizeof(char), 0);
	    strlcpy(value[0], Z_STRVAL_P(attr_value), Z_STRLEN_P(attr_value) + 1);
	    attr_type[0] = CCI_A_TYPE_STR;

	    break;
	case IS_ARRAY:
	    cubrid_retval = cubrid_make_set(HASH_OF(attr_value), &temp_set);
	    if (cubrid_retval < 0) {
		handle_error(cubrid_retval, NULL);
		goto ERR_CUBRID_PUT;
	    }

	    value[0] = temp_set;
	    attr_type[0] = CCI_A_TYPE_SET;

	    break;
	case IS_OBJECT:
	case IS_BOOL:
	case IS_RESOURCE:
	case IS_CONSTANT:
	    cubrid_retval = -1;
	    handle_error(CUBRID_ER_NOT_SUPPORTED_TYPE, NULL);
	    goto ERR_CUBRID_PUT;
	}
	value[1] = NULL;

	break;
    default:
	WRONG_PARAM_COUNT;
    }

    cubrid_retval = cci_oid_put2(connect->handle, oid, attr_name, value, attr_type, &error);
    if (cubrid_retval < 0) {
	handle_error(cubrid_retval, &error);
	goto ERR_CUBRID_PUT;
    }

ERR_CUBRID_PUT:

    if (attr_name) {
	for (i = 0; i < attr_count; i++) {
	    if (attr_name[i])
		efree(attr_name[i]);
	}
	efree(attr_name);
    }

    if (value) {
	for (i = 0; i < attr_count; i++) {
	    switch (attr_type[i]) {
	    case CCI_A_TYPE_SET:
		cci_set_free(value[i]);
		break;
	    default:
		if (value[i]) {
		    efree(value[i]);
		}
		break;
	    }
	}
	efree(value);
    }

    if (attr_type) {
	efree(attr_type);
    }

    if (cubrid_retval < 0) {
	RETURN_FALSE;
    } else {
	RETURN_TRUE;
    }
}

ZEND_FUNCTION(cubrid_drop)
{
    zval *conn_id = NULL;
    char *oid = NULL;
    int oid_len;

    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rs", &conn_id, &oid, &oid_len) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if ((cubrid_retval = cci_oid(connect->handle, CCI_OID_DROP, oid, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE; 
    }

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_is_instance)
{
    zval *conn_id = NULL;
    char *oid = NULL;
    int oid_len;

    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rs", &conn_id, &oid, &oid_len) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if ((cubrid_retval = cci_oid(connect->handle, CCI_OID_IS_INSTANCE, oid, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_LONG(-1);
    }

    RETURN_LONG(cubrid_retval);
}

ZEND_FUNCTION(cubrid_lock_read)
{
    zval *conn_id = NULL;
    char *oid = NULL;
    int oid_len;

    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rs", &conn_id, &oid, &oid_len) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if ((cubrid_retval = cci_oid(connect->handle, CCI_OID_LOCK_READ, oid, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE; 
    }

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_lock_write)
{
    zval *conn_id = NULL;
    char *oid = NULL;
    int oid_len;

    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rs", &conn_id, &oid, &oid_len) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if ((cubrid_retval = cci_oid(connect->handle, CCI_OID_LOCK_WRITE, oid, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE; 
    }

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_get_class_name)
{
    zval *conn_id = NULL;
    char *oid = NULL;
    int oid_len;

    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;
    int cubrid_retval = 0;
    char out_buf[1024];

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rs", &conn_id, &oid, &oid_len) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if ((cubrid_retval = cci_oid_get_class_name(connect->handle, oid, out_buf, 1024, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    RETURN_STRING(out_buf, 1);
}

ZEND_FUNCTION(cubrid_schema)
{
    zval *conn_id = NULL;
    char *class_name = NULL, *attr_name = NULL;
    long schema_type;
    int class_name_len, attr_name_len;

    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;

    int flag = 0;
    int cubrid_retval = 0;
    int request_handle;
    int i = 0;
    zval *temp_element = NULL;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rl|ss", 
		&conn_id, &schema_type, &class_name, &class_name_len, 
		&attr_name, &attr_name_len) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    switch (schema_type) {
    case CUBRID_SCH_CLASS:
    case CUBRID_SCH_VCLASS:
	flag = CCI_CLASS_NAME_PATTERN_MATCH;
	break;
    case CUBRID_SCH_ATTRIBUTE:
    case CUBRID_SCH_CLASS_ATTRIBUTE:
	flag = CCI_ATTR_NAME_PATTERN_MATCH;
	break;
    default:
	flag = 0;
	break;
    }

    if ((cubrid_retval = cci_schema_info(connect->handle, 
		    schema_type, class_name, attr_name, (char) flag, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_LONG(-1);
    }
    request_handle = cubrid_retval;

    array_init(return_value);

    for (i = 0; ; i++) {
	cubrid_retval = cci_cursor(request_handle, 1, CCI_CURSOR_CURRENT, &error);
	if (cubrid_retval == CCI_ER_NO_MORE_DATA) {
	    break;
	}

	if (cubrid_retval < 0) {
	    handle_error(cubrid_retval, &error);
            goto ERR_CUBRID_SCHEMA;
	}

	if ((cubrid_retval = cci_fetch(request_handle, &error)) < 0) {
	    handle_error(cubrid_retval, &error);
            goto ERR_CUBRID_SCHEMA;
	}

	MAKE_STD_ZVAL(temp_element);
	if ((cubrid_retval = fetch_a_row(temp_element, request_handle, 
			CUBRID_ASSOC TSRMLS_CC)) != SUCCESS) {
	    handle_error(cubrid_retval, NULL);
	    FREE_ZVAL(temp_element);
            goto ERR_CUBRID_SCHEMA;
	}

	zend_hash_index_update(Z_ARRVAL_P(return_value), i, (void *) &temp_element, sizeof(zval *), NULL);
    }

    cci_close_req_handle(request_handle);

    return;

ERR_CUBRID_SCHEMA:

    cubrid_array_destroy(return_value->value.ht ZEND_FILE_LINE_CC);
    cci_close_req_handle(request_handle);

    RETURN_LONG(-1);
}

ZEND_FUNCTION(cubrid_col_size)
{
    zval *conn_id = NULL;
    char *oid = NULL, *attr_name = NULL;
    int oid_len, attr_name_len;

    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;
    int cubrid_retval = 0;
    int col_size = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rss", 
		&conn_id, &oid, &oid_len, &attr_name, &attr_name_len) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);
    
    if ((cubrid_retval = cci_col_size(connect->handle, oid, attr_name, &col_size, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_LONG(-1);
    }

    RETURN_LONG(col_size);
}

ZEND_FUNCTION(cubrid_col_get)
{
    zval *conn_id = NULL;
    char *oid = NULL, *attr_name = NULL;
    int oid_len, attr_name_len;

    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    char *res_buf;
    int ind;
    int col_size;
    int col_type;
    int request_handle;
    int i = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rss", 
		&conn_id, &oid, &oid_len, &attr_name, &attr_name_len) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if ((cubrid_retval = cci_col_get(connect->handle, oid, attr_name, &col_size, &col_type, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    request_handle = cubrid_retval;

    array_init(return_value);

    for (i = 0; ;i++) {
	cubrid_retval = cci_cursor(request_handle, 1, CCI_CURSOR_CURRENT, &error);
	if (cubrid_retval == CCI_ER_NO_MORE_DATA) {
	    break;
	}

	if (cubrid_retval < 0) {
	    handle_error(cubrid_retval, &error);
            goto ERR_CUBRID_COL_GET;
	}

	if ((cubrid_retval = cci_fetch(request_handle, &error)) < 0) {
	    handle_error(cubrid_retval, &error);
            goto ERR_CUBRID_COL_GET;
	}

	if ((cubrid_retval = cci_get_data(request_handle, 1, CCI_A_TYPE_STR, &res_buf, &ind)) < 0) {
	    handle_error(cubrid_retval, NULL);
            goto ERR_CUBRID_COL_GET;
	}

	if (ind < 0) {
	    add_index_unset(return_value, i);
	} else {
	    add_index_stringl(return_value, i, res_buf, ind, 1);
	}
    }

    cci_close_req_handle(request_handle);

    return;

ERR_CUBRID_COL_GET:

    cubrid_array_destroy(return_value->value.ht ZEND_FILE_LINE_CC);
    cci_close_req_handle(request_handle);

    RETURN_FALSE;
}

ZEND_FUNCTION(cubrid_set_add)
{
    zval *conn_id = NULL;
    char *oid = NULL, *attr_name = NULL, *set_element = NULL;
    int oid_len, attr_name_len, set_element_len;

    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rsss", 
		&conn_id, &oid, &oid_len, &attr_name, &attr_name_len, 
		&set_element, &set_element_len) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if ((cubrid_retval = cci_col_set_add(connect->handle, oid, attr_name, set_element, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_set_drop)
{
    zval *conn_id = NULL;
    char *oid = NULL, *attr_name = NULL, *set_element = NULL;
    int oid_len, attr_name_len, set_element_len;

    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rsss", 
		&conn_id, &oid, &oid_len, &attr_name, &attr_name_len, 
		&set_element, &set_element_len) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if ((cubrid_retval = cci_col_set_drop(connect->handle, oid, attr_name, set_element, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_seq_insert)
{
    zval *conn_id = NULL;
    char *oid = NULL, *attr_name = NULL, *seq_element = NULL;
    long index = -1;
    int oid_len, attr_name_len, seq_element_len;

    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rssls", 
		&conn_id, &oid, &oid_len, &attr_name, &attr_name_len, &index, 
		&seq_element, &seq_element_len) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if ((cubrid_retval = cci_col_seq_insert(connect->handle, oid, attr_name, index, seq_element, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_seq_put)
{
    zval *conn_id = NULL;
    char *oid = NULL, *attr_name = NULL, *seq_element = NULL;
    long index = -1;
    int oid_len, attr_name_len, seq_element_len;

    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rssls", 
		&conn_id, &oid, &oid_len, &attr_name, &attr_name_len, &index, 
		&seq_element, &seq_element_len) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if ((cubrid_retval = cci_col_seq_put(connect->handle, oid, attr_name, index, seq_element, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_seq_drop)
{
    zval *conn_id = NULL;
    char *oid = NULL, *attr_name = NULL;
    long index = -1;
    int oid_len, attr_name_len;

    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rssl", 
		&conn_id, &oid, &oid_len, &attr_name, &attr_name_len, &index) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if ((cubrid_retval = cci_col_seq_drop(connect->handle, oid, attr_name, index, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_commit)
{
    zval *conn_id = NULL;
    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &conn_id) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if ((cubrid_retval = cci_end_tran(connect->handle, CCI_TRAN_COMMIT, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_rollback)
{
    zval *conn_id = NULL;
    
    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &conn_id) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if ((cubrid_retval = cci_end_tran(connect->handle, CCI_TRAN_ROLLBACK, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_new_glo)
{
    zval *conn_id = NULL;
    char *class_name = NULL, *file_name = NULL;
    int class_name_len, file_name_len;

    T_CUBRID_CONNECT *connect;
    int cubrid_retval = 0;

    char oid_buf[1024];
    T_CCI_ERROR error;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rss", 
		&conn_id, &class_name, &class_name_len, &file_name, &file_name_len) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if ((cubrid_retval = cci_glo_new(connect->handle, class_name, file_name, oid_buf, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    RETURN_STRING(oid_buf, 1);
}

ZEND_FUNCTION(cubrid_save_to_glo)
{
    zval *conn_id = NULL;
    char *oid = NULL, *file_name = NULL;
    int oid_len, file_name_len;

    T_CUBRID_CONNECT *connect;
    int cubrid_retval = 0;
    T_CCI_ERROR error;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rss", 
		&conn_id, &oid, &oid_len, &file_name, &file_name_len) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);
    
    if ((cubrid_retval = cci_glo_save(connect->handle, oid, file_name, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_load_from_glo)
{
    zval *conn_id = NULL;
    char *oid = NULL, *file_name = NULL;
    int oid_len, file_name_len;

    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rss", 
		&conn_id, &oid, &oid_len, &file_name, &file_name_len) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if ((cubrid_retval = cci_glo_load_file_name(connect->handle, oid, file_name, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_send_glo)
{
    zval *conn_id = NULL;
    char *oid = NULL;
    int oid_len;

    T_CUBRID_CONNECT *connect;
    int cubrid_retval = 0;
    T_CCI_ERROR error;

    char out_buf[1024];
    int buf_len;

    char *temp_name = NULL;
    FILE *fp = NULL;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rs", &conn_id, &oid, &oid_len) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    temp_name = tempnam(NULL, "phpcub");
    if (temp_name == NULL) {
	handle_error(CUBRID_ER_CREATE_TEMP_FILE, NULL);
	RETURN_FALSE;
    }

    if ((cubrid_retval = cci_glo_load_file_name(connect->handle, oid, temp_name, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	cubrid_retval = -1;
	goto ERR_CUBRID_SEND_GLO;
    }

    if ((fp = fopen(temp_name, "rb")) == NULL) {
	handle_error(CUBRID_ER_OPEN_FILE, NULL);
	cubrid_retval = -1;
	goto ERR_CUBRID_SEND_GLO;
    }

    while (!feof(fp)) {
	buf_len = fread(out_buf, sizeof(char), 1024, fp);
	if (PHPWRITE(out_buf, buf_len) != buf_len) {
	    handle_error(CUBRID_ER_TRANSFER_FAIL, NULL);
	    cubrid_retval = -1;
	    goto ERR_CUBRID_SEND_GLO;
	}
    }

ERR_CUBRID_SEND_GLO:

    if (fp) {
	fclose(fp);
    }

    if (remove(temp_name) != 0) {
	handle_error(CUBRID_ER_REMOVE_FILE, NULL);
	RETURN_FALSE;
    }

    if (temp_name) {
	free(temp_name);
	temp_name = NULL;
    }

    if (cubrid_retval < 0) {
	RETURN_FALSE;
    } else {
	RETURN_TRUE;
    }
}

ZEND_FUNCTION(cubrid_error_msg)
{
    if (zend_parse_parameters_none() == FAILURE) {
	return;
    }

    RETURN_STRING(CUBRID_G(recent_error).msg, 1);
}

ZEND_FUNCTION(cubrid_error_code)
{
    if (zend_parse_parameters_none() == FAILURE) {
	return;
    }

    RETURN_LONG(CUBRID_G(recent_error).code);
}

ZEND_FUNCTION(cubrid_error_code_facility)
{
    if (zend_parse_parameters_none() == FAILURE) {
	return;
    }

    RETURN_LONG(CUBRID_G(recent_error).facility);
}

ZEND_FUNCTION(cubrid_field_name)
{
    zval *req_id = NULL;
    long offset = -1;
    T_CUBRID_REQUEST *request;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rl", &req_id, &offset) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    if (offset < 0 || offset >= request->col_count) {
	handle_error(CUBRID_ER_INVALID_PARAM, NULL);
	RETURN_FALSE;
    }

    switch (request->sql_type) {
    case CUBRID_STMT_SELECT:
	RETURN_STRING(request->col_info[offset].col_name, 1);
    default:
	handle_error(CUBRID_ER_INVALID_SQL_TYPE, NULL);
	RETURN_LONG(-1);
    }
}

ZEND_FUNCTION(cubrid_field_table)
{
    zval *req_id = NULL;
    long offset = -1;
    T_CUBRID_REQUEST *request;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rl", &req_id, &offset) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    if (offset < 0 || offset >= request->col_count) {
	handle_error(CUBRID_ER_INVALID_PARAM, NULL);
	RETURN_FALSE;
    }

    switch (request->sql_type) {
    case CUBRID_STMT_SELECT:
	RETURN_STRING(request->col_info[offset].class_name, 1);
    default:
	handle_error(CUBRID_ER_INVALID_SQL_TYPE, NULL);
	RETURN_LONG(-1);
    }
}

ZEND_FUNCTION(cubrid_field_type)
{
    zval *req_id = NULL;
    long offset = -1;
    T_CUBRID_REQUEST *request;
    char string_type[128];

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rl", &req_id, &offset) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    if (offset < 0 || offset >= request->col_count) {
	handle_error(CUBRID_ER_INVALID_PARAM, NULL);
	RETURN_FALSE;
    }

    switch (request->sql_type) {
    case CUBRID_STMT_SELECT:
	type2str(&request->col_info[offset], string_type, sizeof(string_type));
	RETURN_STRING(string_type, 1);
    default:
	handle_error(CUBRID_ER_INVALID_SQL_TYPE, NULL);
	RETURN_LONG(-1);
    }
}

ZEND_FUNCTION(cubrid_field_flags)
{
    zval *req_id = NULL; 
    long offset = -1;
    T_CUBRID_REQUEST *request;
    int n;
    char sz[1024];

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rl", &req_id, &offset) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    if (offset < 0 || offset >= request->col_count) {
	handle_error(CUBRID_ER_INVALID_PARAM, NULL);
	RETURN_FALSE;
    }

    strlcpy(sz, "", sizeof(sz));

    if (request->col_info[offset].is_non_null) {
	strcat(sz, "not_null ");
    }

    if (request->col_info[offset].is_primary_key) {
	strcat(sz, "primary_key ");
    }

    if (request->col_info[offset].is_unique_key) {
	strcat(sz, "unique_key ");
    }

    if (request->col_info[offset].is_foreign_key) {
	strcat(sz, "foreign_key ");
    }

    if (request->col_info[offset].is_auto_increment) {
	strcat(sz, "auto_increment ");
    }

    if (request->col_info[offset].is_shared) {
	strcat(sz, "shared ");
    }

    if (request->col_info[offset].is_reverse_index) {
	strcat(sz, "reverse_index ");
    }

    if (request->col_info[offset].is_reverse_unique) {
	strcat(sz, "reverse_unique ");
    }

    if (request->col_info[offset].type == CCI_U_TYPE_TIMESTAMP) {
	strcat(sz, "timestamp ");
    }

    n = strlen(sz);
    if (n > 0 && sz[n - 1] == ' ') {
	sz[n - 1] = 0;
    }

    switch (request->sql_type) {
    case CUBRID_STMT_SELECT:
	RETURN_STRING(sz, 1);
    default:
	handle_error(CUBRID_ER_INVALID_SQL_TYPE, NULL);
	RETURN_LONG(-1);
    }
}

ZEND_FUNCTION(cubrid_data_seek)
{
    zval *req_id = NULL;
    long offset = -1;

    T_CUBRID_REQUEST *request;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rl", &req_id, &offset) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    if (request->row_count == 0) {
	php_error_docref(NULL TSRMLS_CC, E_WARNING, "Number of rows is NULL.\n");
	RETURN_FALSE;
    } else if (offset >= request->row_count || offset < 0) {
	RETURN_FALSE;
    }

    cubrid_retval = cci_cursor(request->handle, offset + 1, CUBRID_CURSOR_FIRST, &error);
    if (cubrid_retval == CCI_ER_NO_MORE_DATA) {
	RETURN_LONG(CUBRID_NO_MORE_DATA);
    }

    if (cubrid_retval < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    RETURN_LONG(CUBRID_CURSOR_SUCCESS);
}

ZEND_FUNCTION(cubrid_fetch_array)
{
    zval *req_id = NULL;
    long type = CUBRID_BOTH;

    T_CUBRID_REQUEST *request;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r|l", &req_id, &type) == FAILURE) {
	return;
    }

    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    cubrid_retval = cci_cursor(request->handle, 0, CCI_CURSOR_CURRENT, &error);
    if (cubrid_retval == CCI_ER_NO_MORE_DATA) {
	RETURN_FALSE;
    }

    cubrid_retval = cci_fetch(request->handle, &error);
    if (cubrid_retval < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    if ((cubrid_retval = fetch_a_row(return_value, request->handle, type TSRMLS_CC)) != SUCCESS) {
	handle_error(cubrid_retval, NULL);
	RETURN_FALSE;
    }

    cubrid_retval = cci_cursor(request->handle, 1, CCI_CURSOR_CURRENT, &error);
    if (cubrid_retval < 0 && cubrid_retval != CCI_ER_NO_MORE_DATA) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    return;
}

ZEND_FUNCTION(cubrid_fetch_assoc)
{
    zval *req_id = NULL;
    T_CUBRID_REQUEST *request;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &req_id) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    if ((cubrid_retval = cci_cursor(request->handle, 0, 
		    CCI_CURSOR_CURRENT, &error)) == CCI_ER_NO_MORE_DATA) {
	RETURN_FALSE;
    }

    if ((cubrid_retval = cci_fetch(request->handle, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    if ((cubrid_retval = fetch_a_row(return_value, request->handle, 
		    CUBRID_ASSOC TSRMLS_CC)) != SUCCESS) {
	handle_error(cubrid_retval, NULL);
	RETURN_FALSE;
    }

    cubrid_retval = cci_cursor(request->handle, 1, CCI_CURSOR_CURRENT, &error);
    if (cubrid_retval < 0 && cubrid_retval != CCI_ER_NO_MORE_DATA) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    return;
}

ZEND_FUNCTION(cubrid_fetch_row)
{
    zval *req_id = NULL;
    T_CUBRID_REQUEST *request;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &req_id) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    if ((cubrid_retval = cci_cursor(request->handle, 0, 
		    CCI_CURSOR_CURRENT, &error)) == CCI_ER_NO_MORE_DATA) {
	RETURN_FALSE;
    }

    if ((cubrid_retval = cci_fetch(request->handle, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    if ((cubrid_retval = fetch_a_row(return_value, request->handle, CUBRID_NUM TSRMLS_CC)) != SUCCESS) {
	handle_error(cubrid_retval, NULL);
	RETURN_FALSE;
    }

    cubrid_retval = cci_cursor(request->handle, 1, CCI_CURSOR_CURRENT, &error);
    if (cubrid_retval < 0 && cubrid_retval != CCI_ER_NO_MORE_DATA) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    return;
}

ZEND_FUNCTION(cubrid_fetch_field)
{
    zval *req_id = NULL;
    long offset = 0;
    T_CUBRID_REQUEST *request = NULL;

    zend_bool is_numeric = 0;
    int max_length = 0;
    T_CCI_ERROR error;

    int res = 0, ind = 0, col = 0;
    char *buffer = NULL;
    char string_type[128];

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r|l", &req_id, &offset) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    if (ZEND_NUM_ARGS() == 1) {
	offset = request->fetch_field_auto_index++;
    } else if (ZEND_NUM_ARGS() == 2) {
	request->fetch_field_auto_index = offset + 1;
    }

    if (offset < 0 || offset >= request->col_count) {
	handle_error(CUBRID_ER_INVALID_PARAM, NULL);
	RETURN_FALSE;
    }

    array_init(return_value);

    is_numeric = numeric_type(request->col_info[offset].type);
    max_length = 0;

    col = 1;
    while (1) {
	res = cci_cursor(request->handle, col++, CCI_CURSOR_FIRST, &error);
	if (res == CCI_ER_NO_MORE_DATA) {
	    break;
	}

	if (res < 0) {
	    handle_error(res, &error);
            goto ERR_CUBRID_FETCH_FIELD;
	}

	if ((res = cci_fetch(request->handle, &error)) < 0) {
	    handle_error(res, &error);
            goto ERR_CUBRID_FETCH_FIELD;
	}

	buffer = NULL;
	if ((res = cci_get_data(request->handle, offset + 1, CCI_A_TYPE_STR, &buffer, &ind)) < 0) {
	    handle_error(res, &error);
            goto ERR_CUBRID_FETCH_FIELD;
	}

	if (ind > max_length) {
	    max_length = ind;
	}
    }

    add_assoc_string(return_value, "name", request->col_info[offset].col_name, 1);
    add_assoc_string(return_value, "table", request->col_info[offset].class_name, 1);
    add_assoc_string(return_value, "def", request->col_info[offset].default_value, 1);
    add_assoc_long(return_value, "max_length", max_length);
    add_assoc_long(return_value, "not_null", request->col_info[offset].is_non_null);
    add_assoc_long(return_value, "unique_key", request->col_info[offset].is_unique_key);
    add_assoc_long(return_value, "multiple_key", !request->col_info[offset].is_unique_key);
    add_assoc_long(return_value, "numeric", is_numeric);

    type2str(&request->col_info[offset], string_type, sizeof(string_type));
    add_assoc_string(return_value, "type", string_type, 1);

    if (return_value->type == IS_ARRAY) {
	convert_to_object(return_value);
    }

    return;

ERR_CUBRID_FETCH_FIELD:

    cubrid_array_destroy(return_value->value.ht ZEND_FILE_LINE_CC);

    RETURN_FALSE;
}

ZEND_FUNCTION(cubrid_num_fields)
{
    zval *req_id = NULL;
    T_CUBRID_REQUEST *request;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &req_id) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    switch (request->sql_type) {
    case CUBRID_STMT_SELECT:
	RETURN_LONG(request->col_count);
    default:
	handle_error(CUBRID_ER_INVALID_SQL_TYPE, NULL);
	RETURN_LONG(-1);
    }
}

ZEND_FUNCTION(cubrid_free_result)
{
    zval *req_id = NULL;
    T_CUBRID_REQUEST *request;
    int cubrid_retval = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &req_id) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    if ((cubrid_retval = cci_fetch_buffer_clear(request->handle)) < 0) {
	handle_error(cubrid_retval, NULL); 
	RETURN_FALSE;
    }

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_fetch_lengths)
{
    zval *req_id = NULL;

    T_CUBRID_REQUEST *request;
    T_CCI_ERROR error;
    int col, ind, res;
    long len = 0;
    char *buffer;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &req_id) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    res = cci_cursor(request->handle, 0, CCI_CURSOR_CURRENT, &error);
    if (res == CCI_ER_NO_MORE_DATA) {
	RETURN_FALSE;
    }

    if (res < 0) {
	handle_error(res, &error);
	RETURN_FALSE;
    }

    array_init(return_value);

    for (col = 0; col < request->col_count; col++) {
	if ((res = cci_get_data(request->handle, col + 1, CCI_A_TYPE_STR, &buffer, &ind)) < 0) {
	    handle_error(res, &error);
            cubrid_array_destroy(return_value->value.ht ZEND_FILE_LINE_CC);
	    RETURN_FALSE;
	}

	if (ind != -1) {
	    len = ind;
	} else {
	    len = 0;
	}

	add_index_long(return_value, col, len);
    }

    return;
}

ZEND_FUNCTION(cubrid_fetch_object)
{
    zval *req_id = NULL, *ctor_params = NULL;
    char *class_name = NULL;
    int class_name_len = 0;

    T_CUBRID_REQUEST *request;
    T_CCI_ERROR error;
    int cubrid_retval = 0;

    zend_class_entry *ce = NULL;

    zval dataset;
    zend_fcall_info fci;
    zend_fcall_info_cache fcc;
    zval *retval_ptr;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r|sz", 
                &req_id, &class_name, &class_name_len, &ctor_params) == FAILURE) {
	return;
    }
    
    if (ZEND_NUM_ARGS() < 2) {
        ce = zend_standard_class_def;
    } else {
        ce = zend_fetch_class(class_name, class_name_len, ZEND_FETCH_CLASS_AUTO TSRMLS_CC);
    }

    if (!ce) {
        php_error_docref(NULL TSRMLS_CC, E_WARNING, "Could not find class '%s'", class_name);
        return;
    }

    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    /* get cursor at current position in the returned recordset */
    if ((cubrid_retval = cci_cursor(request->handle, 0, CCI_CURSOR_CURRENT, &error)) == CCI_ER_NO_MORE_DATA) {
	RETURN_FALSE;
    }

    if ((cubrid_retval = cci_fetch(request->handle, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    if ((cubrid_retval = fetch_a_row(return_value, request->handle, CUBRID_BOTH | CUBRID_OBJECT TSRMLS_CC)) != SUCCESS) {
	handle_error(cubrid_retval, NULL);
	RETURN_FALSE;
    }

    /* convert to object, method learned from mysql extension */ 
    dataset = *return_value;

    object_and_properties_init(return_value, ce, NULL);
    zend_merge_properties(return_value, Z_ARRVAL(dataset), 1 TSRMLS_CC);

    if (ce->constructor) {
        fci.size = sizeof(fci);
        fci.function_table = &ce->function_table;
        fci.function_name = NULL;
        fci.symbol_table = NULL;
#if PHP_MINOR_VERSION < 3
        fci.object_pp = &return_value;
#else
        fci.object_ptr = return_value;
#endif
        fci.retval_ptr_ptr = &retval_ptr;
        if (ctor_params && Z_TYPE_P(ctor_params) != IS_NULL) {
            if (Z_TYPE_P(ctor_params) == IS_ARRAY) {
                HashTable *ht = Z_ARRVAL_P(ctor_params);
                Bucket *p;

                fci.param_count = 0;
                fci.params = safe_emalloc(sizeof(zval*), ht->nNumOfElements, 0);
                p = ht->pListHead;
                while (p != NULL) {
                    fci.params[fci.param_count++] = (zval**)p->pData;
                    p = p->pListNext;
                }
            } else {
                /* Two problems why we throw exceptions here: PHP is typeless
                 * and hence passing one argument that's not an array could be
                 * by mistake and the other way round is possible, too. The 
                 * single value is an array. Also we'd have to make that one
                 * argument passed by reference.
                 */
                zend_throw_exception(zend_exception_get_default(TSRMLS_C), 
                        "Parameter ctor_params must be an array", 0 TSRMLS_CC);
                return;
            }
        } else {
            fci.param_count = 0;
            fci.params = NULL;
        }
        fci.no_separation = 1;

        fcc.initialized = 1;
        fcc.function_handler = ce->constructor;
        fcc.calling_scope = EG(scope);
#if PHP_MINOR_VERSION < 3
        fcc.object_pp = &return_value;
#else
        fcc.called_scope = Z_OBJCE_P(return_value);
        fcc.object_ptr = return_value;
#endif

        if (zend_call_function(&fci, &fcc TSRMLS_CC) == FAILURE) {
            zend_throw_exception_ex(zend_exception_get_default(TSRMLS_C), 0 TSRMLS_CC, "Could not execute %s::%s()", ce->name, ce->constructor->common.function_name);
        } else {
            if (retval_ptr) {
                zval_ptr_dtor(&retval_ptr);
            }
        }
        if (fci.params) {
            efree(fci.params);
        }
    } else if (ctor_params) {
        zend_throw_exception_ex(zend_exception_get_default(TSRMLS_C), 0 TSRMLS_CC, "Class %s does not have a constructor hence you cannot use ctor_params", ce->name);
    }

    if (Z_TYPE_P(return_value) == IS_ARRAY) {
        object_and_properties_init(return_value, ZEND_STANDARD_CLASS_DEF_PTR, Z_ARRVAL_P(return_value));
    }

    /* advance current recordset position with one row */
    cubrid_retval = cci_cursor(request->handle, 1, CCI_CURSOR_CURRENT, &error);
    if (cubrid_retval < 0 && cubrid_retval != CCI_ER_NO_MORE_DATA) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    return;
}

ZEND_FUNCTION(cubrid_field_seek)
{
    zval *req_id = NULL;
    long offset = 0;
    T_CUBRID_REQUEST *request = NULL;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r|l", &req_id, &offset) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    if (offset < 0 || offset > request->col_count - 1) {
	handle_error(CUBRID_ER_INVALID_PARAM, NULL);
	RETURN_FALSE;
    }

    /* Set the offset which will be used by cubrid_fetch_field() */
    request->fetch_field_auto_index = offset;

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_field_len)
{
    zval *req_id = NULL;
    long offset = 0;

    T_CUBRID_REQUEST *request;
    long len = 0;
    T_CCI_U_TYPE type;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rl", &req_id, &offset) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    if (offset < 0 || offset > request->col_count - 1) {
	handle_error(CUBRID_ER_INVALID_PARAM, NULL);
	RETURN_FALSE;
    }

    type = CCI_GET_COLLECTION_DOMAIN(CCI_GET_RESULT_INFO_TYPE(request->col_info, offset + 1));
    if ((len = get_cubrid_u_type_len(type)) == -1) {
	len = CCI_GET_RESULT_INFO_PRECISION(request->col_info, offset + 1); 
	if (type == CCI_U_TYPE_NUMERIC) {
	    len += 2; /* "," + "-" */
	}
    }

    if (CCI_IS_COLLECTION_TYPE(CCI_GET_RESULT_INFO_TYPE(request->col_info, offset + 1))) {
	len = MAX_LEN_SET;
    }

    RETURN_LONG(len);
}

ZEND_FUNCTION(cubrid_result)
{
    zval *req_id = NULL;
    long row_offset = 0;
    zval *column = NULL;

    long col_offset = 0;
    char *col_name = NULL;
    long col_name_len = 0;

    int cubrid_retval = 0;
    int i;

    T_CUBRID_REQUEST *request = NULL;
    T_CCI_ERROR error;

    char *res_buf = NULL;
    int ind = 0;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "rl|z", &req_id, &row_offset, &column) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(request, T_CUBRID_REQUEST *, &req_id, -1, "CUBRID-Request", le_request);

    if (column) {
	switch (Z_TYPE_P(column)) {
	case IS_STRING:
	    convert_to_string_ex(&column);
	    col_name = Z_STRVAL_P(column);
	    col_name_len = Z_STRLEN_P(column);

	    for (i = 0; i < request->col_count; i++) {
		if (strcmp(request->col_info[i].col_name, col_name) == 0) {
		    col_offset = i;
		    break;
		}
	    }

	    if (i == request->col_count) {
		handle_error(CUBRID_ER_INVALID_PARAM, NULL);
		RETURN_FALSE;
	    }

	    break;
	case IS_LONG:
	    convert_to_long_ex(&column);
	    col_offset = Z_LVAL_P(column); 

	    if (col_offset < 0 || col_offset >= request->col_count) {
		handle_error(CUBRID_ER_INVALID_PARAM, NULL);
		RETURN_FALSE;
	    }

	    break;
	default:
	    handle_error(CUBRID_ER_INVALID_PARAM, NULL);
	    RETURN_FALSE;
	}
    }

    if ((cubrid_retval = cci_cursor(request->handle, row_offset + 1, 
		    CCI_CURSOR_FIRST, &error)) == CCI_ER_NO_MORE_DATA) {
	RETURN_FALSE;
    }

    if ((cubrid_retval = cci_fetch(request->handle, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    if ((cubrid_retval = cci_get_data(request->handle, col_offset + 1, 
		    CCI_A_TYPE_STR, &res_buf, &ind)) < 0) {
	handle_error(cubrid_retval, NULL);
	RETURN_FALSE;
    }

    if (ind < 0) {
	RETURN_FALSE; 
    } else {
	RETURN_STRINGL(res_buf, ind, 1);
    }
}

ZEND_FUNCTION (cubrid_unbuffered_query)
{
    zval *conn_id = NULL;
    char *query = NULL;
    int query_len;

    T_CUBRID_CONNECT *connect = NULL;
    T_CUBRID_REQUEST *request = NULL;
    T_CCI_ERROR error;

    T_CCI_COL_INFO *res_col_info = NULL;
    T_CCI_CUBRID_STMT res_sql_type = 0;
    int res_col_count = 0;

    int cubrid_retval = 0;
    int req_handle = 0;
    int req_id = 0;

    init_error ();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s|r", &query, &query_len, &conn_id) == FAILURE) {
	return;
    }

    if (conn_id){
        ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);
    } else {
	if (CUBRID_G(last_connect_id) == -1) {
            RETURN_FALSE; 
        } 

        ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, NULL, CUBRID_G(last_connect_id), "CUBRID-Connect", le_connect);
    }

    request = new_cubrid_request();
    req_id = ZEND_REGISTER_RESOURCE(return_value, request, le_request);

    if ((cubrid_retval = cci_prepare(connect->handle, query, 0, &error)) < 0) {
	handle_error(cubrid_retval, &error);
        RETURN_FALSE;
    }

    req_handle = cubrid_retval;
    request->handle = req_handle;
    request->async_mode = 1;

    if ((cubrid_retval = cci_execute(req_handle, CCI_EXEC_ASYNC, 0, &error)) < 0) {
	handle_error(cubrid_retval, &error);
        RETURN_FALSE;
    }

    res_col_info = cci_get_result_info(req_handle, &res_sql_type, &res_col_count);
    request->sql_type = res_sql_type;

    if (res_sql_type == CUBRID_STMT_SELECT && !res_col_info) {
        RETURN_FALSE;
    } else if (res_sql_type == CUBRID_STMT_SELECT) {
        request->col_info = res_col_info;
        request->col_count = res_col_count;
    }

    CUBRID_G(last_request_stmt_type) = request->sql_type;
    request->fetch_field_auto_index = 0;

    switch (request->sql_type) {
    case CUBRID_STMT_SELECT:
	request->row_count = cubrid_retval;

        cubrid_retval = cci_cursor(req_handle, 1, CCI_CURSOR_CURRENT, &error);
        if (cubrid_retval < 0 && cubrid_retval != CCI_ER_NO_MORE_DATA) {
            handle_error(cubrid_retval, &error);
            RETURN_FALSE;
        }

        CUBRID_G(last_request_id) = req_id;
        CUBRID_G(last_request_affected_rows) = 0;

        return;
    case CUBRID_STMT_INSERT:
    case CUBRID_STMT_UPDATE:
    case CUBRID_STMT_DELETE:
	request->affected_rows = cubrid_retval;
        CUBRID_G(last_request_affected_rows) = cubrid_retval;

	break;
    case CUBRID_STMT_CALL:
	request->row_count = cubrid_retval;
        CUBRID_G(last_request_affected_rows) = cubrid_retval;

    default:
	break;
    }

    php_cubrid_set_default_req(Z_LVAL_P(return_value) TSRMLS_CC);

    RETURN_TRUE;
}

ZEND_FUNCTION(cubrid_get_charset)
{
    zval *conn_id = NULL;
    char *query = "SELECT charset FROM db_root";

    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;

    int cubrid_retval = 0;
    int request_handle = 0;

    char *buffer;
    int ind;

    int index = -1;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &conn_id) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    if ((cubrid_retval = cci_prepare(connect->handle, query, 0, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    request_handle = cubrid_retval;

    if ((cubrid_retval = cci_execute(request_handle, CCI_EXEC_ASYNC, 0, &error)) < 0) {
	handle_error(cubrid_retval, &error);
        goto ERR_CUBRID_GET_CHARSET;
    }

    cubrid_retval = cci_cursor(request_handle, 1, CCI_CURSOR_CURRENT, &error);
    if (cubrid_retval < 0 && cubrid_retval != CCI_ER_NO_MORE_DATA) {
	handle_error(cubrid_retval, &error);
        goto ERR_CUBRID_GET_CHARSET;
    }

    if ((cubrid_retval = cci_fetch(request_handle, &error)) < 0) {
	handle_error(cubrid_retval, &error);
        goto ERR_CUBRID_GET_CHARSET;
    }

    if ((cubrid_retval = cci_get_data(request_handle, 1, CCI_A_TYPE_STR, &buffer, &ind)) < 0) {
	handle_error(cubrid_retval, &error);
        goto ERR_CUBRID_GET_CHARSET;
    }

    if (ind != -1) {
	index = atoi(buffer);
    } else {
	RETURN_FALSE;
    }

    if (index < 0 || index > MAX_DB_CHARSETS) {
	index = MAX_DB_CHARSETS;
    }

    RETURN_STRING((char *) db_charsets[index].charset_name, 1);

    cci_close_req_handle(request_handle);
    return;

ERR_CUBRID_GET_CHARSET:

    cci_close_req_handle(request_handle);
    RETURN_FALSE;
}

ZEND_FUNCTION(cubrid_get_client_info)
{
    int major, minor, patch;
    char info[128];

    init_error();

    if (zend_parse_parameters_none() == FAILURE) {
	return;
    }

    cci_get_version(&major, &minor, &patch);

    snprintf(info, sizeof(info), "%d.%d.%d", major, minor, patch);

    RETURN_STRING(info, 1);
}

ZEND_FUNCTION(cubrid_get_server_info)
{
    zval *conn_id = NULL;
    T_CUBRID_CONNECT *connect;

    char buf[64];

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &conn_id) == FAILURE) {
	return;
    }
    
    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    cci_get_db_version(connect->handle, buf, sizeof(buf));

    RETURN_STRING(buf, 1);
}

ZEND_FUNCTION(cubrid_real_escape_string)
{
    zval *conn_id = NULL;

    char *unescaped_str = NULL;
    int unescaped_str_len = 0;

    char *escaped_str;
    int escaped_str_len = 0;

    char *s1, *s2;
    int i;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s|r", 
		&unescaped_str, &unescaped_str_len, &conn_id) == FAILURE) {
	return;
    }

    if (unescaped_str_len > MAX_UNESCAPED_STR_LEN) {
	RETURN_FALSE; 
    }

    s1 = unescaped_str;
    for (i = 0; i < unescaped_str_len; i++) {
	/* cubrid only need to escape single quote */
	if (s1[i] == '\'') {
	    escaped_str_len += 2;
	} else {
	    escaped_str_len++;
	}
    }

    escaped_str = safe_emalloc(escaped_str_len + 1, sizeof(char), 0);

    s1 = unescaped_str;
    s2 = escaped_str;
    for (i = 0; i < unescaped_str_len; i++) {
	if (s1[i] == '\'') {
	    *s2++ = '\\';
	}

	*s2++ = s1[i]; 
    }
    *s2 = '\0';
    
    RETURN_STRINGL(escaped_str, escaped_str_len, 0);
}

ZEND_FUNCTION(cubrid_get_db_parameter)
{
    zval *conn_id = NULL;

    T_CUBRID_CONNECT *connect;
    T_CCI_ERROR error;

    int cubrid_retval = 0;
    int i, val;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &conn_id) == FAILURE) {
	return;
    }

    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    array_init(return_value);

    for (i = CCI_PARAM_FIRST; i <= CCI_PARAM_LAST; i++) {
	if ((cubrid_retval = cci_get_db_parameter(connect->handle, 
			(T_CCI_DB_PARAM) i, (void *) &val, &error)) < 0) {
	    handle_error(cubrid_retval, &error);
            cubrid_array_destroy(return_value->value.ht ZEND_FILE_LINE_CC);
	    RETURN_FALSE;
	}

	add_assoc_long(return_value, (char *) db_parameters[i - 1].parameter_name, val);
    }

    return;
}

ZEND_FUNCTION(cubrid_list_dbs) 
{
    zval *conn_id = NULL;
    char *query = "SELECT LIST_DBS()";

    T_CUBRID_CONNECT *connect = NULL;
    T_CCI_ERROR error;

    int cubrid_retval = 0;
    int request_handle = 0;

    char *buffer = NULL;
    int ind = 0;

    int i;
    char *pos = NULL;

    init_error();

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "r", &conn_id) == FAILURE) {
	return;
    }

    ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);

    array_init(return_value);
   
    if ((cubrid_retval = cci_prepare(connect->handle, query, 0, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	RETURN_FALSE;
    }

    request_handle = cubrid_retval;

    if ((cubrid_retval = cci_execute(request_handle, CCI_EXEC_ASYNC, 0, &error)) < 0) {
	handle_error(cubrid_retval, &error);
        goto ERR_CUBRID_LIST_DBS;
    }

    cubrid_retval = cci_cursor(request_handle, 1, CCI_CURSOR_CURRENT, &error);
    if (cubrid_retval < 0 && cubrid_retval != CCI_ER_NO_MORE_DATA) {
	handle_error(cubrid_retval, &error);
        goto ERR_CUBRID_LIST_DBS;
    }

    if ((cubrid_retval = cci_fetch(request_handle, &error)) < 0) {
	handle_error(cubrid_retval, &error);
        goto ERR_CUBRID_LIST_DBS;
    }

    if ((cubrid_retval = cci_get_data(request_handle, 1, CCI_A_TYPE_STR, &buffer, &ind)) < 0) {
	handle_error(cubrid_retval, &error);
        goto ERR_CUBRID_LIST_DBS;
    }


    /* Databases names are separated by spaces */
    i = 0;
    if (ind != -1) {
	pos = strtok(buffer, " ");
	if (pos) {
	    while (pos != NULL) {
		add_index_stringl(return_value, i++, pos, strlen(pos), 1);
		pos = strtok(NULL, " ");
	    }
	} else {
	    add_index_stringl(return_value, 0, buffer, strlen(buffer), 1);
	}
    } else {
        goto ERR_CUBRID_LIST_DBS;
    }

    cci_close_req_handle(request_handle);
    return;

ERR_CUBRID_LIST_DBS:

    cci_close_req_handle(request_handle);
    cubrid_array_destroy(return_value->value.ht ZEND_FILE_LINE_CC);

    RETURN_FALSE;
}

ZEND_FUNCTION(cubrid_insert_id)
{
    zval *conn_id = NULL;
    char *class_name = NULL;
    int class_name_len;

    T_CUBRID_CONNECT *connect;
    int connect_handle = 0;
    int cubrid_retval = 0;

    char **columns = NULL;
    char **values = NULL;

    int i, count = 0;

    init_error();

    if (CUBRID_G(last_request_id) == -1) {
	RETURN_FALSE;
    }

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s|r", 
		&class_name, &class_name_len, &conn_id) == FAILURE) {
	return;
    }

    if (conn_id) {
	ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, &conn_id, -1, "CUBRID-Connect", le_connect);
    } else {
	if (CUBRID_G(last_connect_id) == -1) {
	    RETURN_FALSE;
	}

        ZEND_FETCH_RESOURCE(connect, T_CUBRID_CONNECT *, NULL, CUBRID_G(last_connect_id), "CUBRID-Connect", le_connect);
    }

    connect_handle = connect->handle;

    array_init(return_value);

    switch (CUBRID_G(last_request_stmt_type)) {
    case CUBRID_STMT_INSERT:
	if (CUBRID_G(last_request_affected_rows) < 1) {
	    RETURN_LONG(0);
	}

	columns = (char **) safe_emalloc(MAX_AUTOINCREMENT_COLS, sizeof(char *), 0);
	values = (char **) safe_emalloc(MAX_AUTOINCREMENT_COLS, sizeof(char *), 0);

	if ((count = get_last_autoincrement(class_name, columns, values, connect_handle)) < 0) {
	   cubrid_retval = -1;
	   goto ERR_CUBRID_INSERT_ID;
	}

	for (i = 0; i < count; i++) {
	    add_assoc_string(return_value, columns[i], values[i], 1);
	}

	break;
    case CUBRID_STMT_SELECT:
    case CUBRID_STMT_UPDATE:
    case CUBRID_STMT_DELETE:
        cubrid_array_destroy(return_value->value.ht ZEND_FILE_LINE_CC);
	RETURN_LONG(0);

    default:
        cubrid_array_destroy(return_value->value.ht ZEND_FILE_LINE_CC);
	handle_error(CUBRID_ER_INVALID_SQL_TYPE, NULL);
	RETURN_FALSE;
    }

ERR_CUBRID_INSERT_ID:

    for (i = 0; i < count; i++) {
 	if (columns[i]) {
	    efree(columns[i]);   
	}

        if (values[i]) {
            efree(values[i]);
        }
    }

    if (columns) {
	efree(columns);
    }

    if (values) {
	efree(values);
    }

    if (cubrid_retval < 0) {
        cubrid_array_destroy(return_value->value.ht ZEND_FILE_LINE_CC);
	RETURN_FALSE;
    }

    return;
}

/************************************************************************
* PRIVATE FUNCTIONS IMPLEMENTATION
************************************************************************/

static void php_cubrid_set_default_conn(int id TSRMLS_DC)
{
    if (CUBRID_G(last_connect_id) != -1) {
        zend_list_delete(CUBRID_G(last_connect_id));
    }

    CUBRID_G(last_connect_id) = id;
    zend_list_addref(id);
}

static void php_cubrid_set_default_req(int id TSRMLS_DC)
{
    if (CUBRID_G(last_request_id) != -1) {
        zend_list_delete(CUBRID_G(last_request_id));
    }

    CUBRID_G(last_request_id) = id;
    zend_list_addref(id);
}

static void close_cubrid_connect(T_CUBRID_CONNECT * conn)
{
    T_CCI_ERROR error;
    cci_disconnect(conn->handle, &error);
    efree(conn);
}

static void close_cubrid_request(T_CUBRID_REQUEST * req)
{
    cci_close_req_handle(req->handle);

    if (req->l_bind) {
	efree(req->l_bind);
    }
    efree(req);
}

static void php_cubrid_init_globals(zend_cubrid_globals * cubrid_globals)
{
    cubrid_globals->recent_error.code = 0;
    cubrid_globals->recent_error.facility = 0;
    cubrid_globals->recent_error.msg[0] = 0;

    cubrid_globals->last_connect_id = -1;
    cubrid_globals->last_request_id = -1;
    cubrid_globals->last_request_stmt_type = 0;
    cubrid_globals->last_request_affected_rows = 0;

    cubrid_globals->default_userid = "PUBLIC";
    cubrid_globals->default_passwd = "";
}

static int init_error(void)
{
    set_error(0, 0, "");

    return SUCCESS;
}

static int set_error(T_FACILITY_CODE facility, int code, char *msg, ...)
{
    va_list args;
    TSRMLS_FETCH();

    CUBRID_G(recent_error).facility = facility;
    CUBRID_G(recent_error).code = code;

    va_start(args, msg);
    snprintf(CUBRID_G(recent_error).msg, 1024, msg, args);
    va_end(args);

    return SUCCESS;
}

static int get_error_msg(int err_code, char *buf, int buf_size)
{
    const char *err_msg = "";
    int size = sizeof(db_error) / sizeof(db_error[0]);
    int i;

    if (err_code > -2000) {
	return cci_get_err_msg(err_code, buf, buf_size);
    }

    for (i = 0; i < size; i++) {
	if (err_code == db_error[i].err_code) {
	    err_msg = db_error[i].err_msg;
	    break;
	}
    }

    if (i == size) {
	err_msg = "Unknown Error";
    }

    strlcpy(buf, err_msg, buf_size);

    return SUCCESS;
}

static int handle_error(int err_code, T_CCI_ERROR * error)
{
    int real_err_code = 0;
    char *real_err_msg = NULL;

    T_FACILITY_CODE facility = CUBRID_FACILITY_CLIENT;

    char err_msg[1024] = { 0 };
    char *facility_msg = NULL;

    if (err_code == CCI_ER_DBMS) {
	facility = CUBRID_FACILITY_DBMS;
	facility_msg = "DBMS";
	if (error) {
	    real_err_code = error->err_code;
	    real_err_msg = error->err_msg;
	} else {
	    real_err_code = 0;
	    real_err_msg = "Unknown DBMS error";
	}
    } else {
	if (err_code > -1000) {
	    facility = CUBRID_FACILITY_CCI;
	    facility_msg = "CCI";
	} else if (err_code > -2000) {
	    facility = CUBRID_FACILITY_CAS;
	    facility_msg = "CAS";
	} else if (err_code > -3000) {
	    facility = CUBRID_FACILITY_CLIENT;
	    facility_msg = "CLIENT";
	} else {
	    real_err_code = -1;
	    real_err_msg = NULL;
	    return FAILURE;
	}

	if (get_error_msg(err_code, err_msg, (int) sizeof(err_msg)) < 0) {
	    strlcpy(err_msg, "Unknown error message", sizeof(err_msg));
	}

	real_err_code = err_code;
	real_err_msg = err_msg;
    }

    set_error(facility, real_err_code, real_err_msg);
    php_error(E_WARNING, "Error: %s, %d, %s", facility_msg, real_err_code, real_err_msg);

    return SUCCESS;
}

static int fetch_a_row(zval *arg, int req_handle, int type TSRMLS_DC)
{
    T_CCI_COL_INFO *column_info = NULL;
    int column_count = 0;
    T_CCI_U_TYPE column_type;
    char *column_name;

    int cubrid_retval = 0;
    int null_indicator;
    int i;

    if ((column_info = cci_get_result_info(req_handle, NULL, &column_count)) == NULL) {
	return CUBRID_ER_CANNOT_GET_COLUMN_INFO;
    }

    array_init(arg);

    for (i = 0; i < column_count; i++) {
	column_type = CCI_GET_RESULT_INFO_TYPE(column_info, i + 1);
	column_name = CCI_GET_RESULT_INFO_NAME(column_info, i + 1);

	if (CCI_IS_SET_TYPE(column_type) || CCI_IS_MULTISET_TYPE(column_type) || 
		CCI_IS_SEQUENCE_TYPE(column_type)) {
	    T_CCI_SET res_buf = NULL;

	    if ((cubrid_retval = cci_get_data(req_handle, i + 1, 
			    CCI_A_TYPE_SET, &res_buf, &null_indicator)) < 0) {
		goto ERR_FETCH_A_ROW;
	    }

	    if (null_indicator < 0) {
		if (type & CUBRID_NUM) {
		    add_index_unset(arg, i);
		}

		if (type & CUBRID_ASSOC) {
		    add_assoc_unset(arg, column_name);
		}
	    } else {
		if (type & CUBRID_NUM) {
		    cubrid_retval = cubrid_add_index_array(arg, i, res_buf TSRMLS_CC);
		} 
		
		if (type & CUBRID_ASSOC) {
		    cubrid_retval = cubrid_add_assoc_array(arg, column_name, res_buf TSRMLS_CC);
		}
		
		if (cubrid_retval < 0) {
		    cci_set_free(res_buf);
		    goto ERR_FETCH_A_ROW;
		}

		cci_set_free(res_buf);
	    }
	} else {
	    char *res_buf = NULL;

	    if ((cubrid_retval = cci_get_data(req_handle, i + 1, 
			    CCI_A_TYPE_STR, &res_buf, &null_indicator)) < 0) {
		goto ERR_FETCH_A_ROW;
	    }

	    if (null_indicator < 0) {
		if (type & CUBRID_NUM) {
		    add_index_unset(arg, i);
		}

		if (type & CUBRID_ASSOC) {
		    add_assoc_unset(arg, column_name);
		}
	    } else {
		if (type & CUBRID_NUM) {
		    add_index_stringl(arg, i, res_buf, null_indicator, 1);
		} 
		
		if (type & CUBRID_ASSOC) {
		    add_assoc_stringl(arg, column_name, res_buf, null_indicator, 1);
		}
	    }
	}
    }

    return SUCCESS;

ERR_FETCH_A_ROW:

    cubrid_array_destroy(arg->value.ht ZEND_FILE_LINE_CC);
    return cubrid_retval;
}

static T_CUBRID_CONNECT *new_cubrid_connect(void)
{
    T_CUBRID_CONNECT *connect = (T_CUBRID_CONNECT *) emalloc(sizeof(T_CUBRID_CONNECT));

    connect->handle = 0;

    return connect;
}

static T_CUBRID_REQUEST *new_cubrid_request(void)
{
    T_CUBRID_REQUEST *request = (T_CUBRID_REQUEST *) emalloc(sizeof(T_CUBRID_REQUEST));

    request->handle = 0;
    request->affected_rows = -1;
    request->async_mode = 0;
    request->row_count = -1;
    request->col_count = -1;
    request->sql_type = 0;
    request->bind_num = -1;
    request->l_bind = NULL;
    request->l_prepare = 0;

    return request;
}

static int cubrid_add_index_array(zval *arg, uint index, T_CCI_SET in_set TSRMLS_DC)
{
    zval *tmp_ptr;

    int i;
    int res;
    int ind;
    char *buffer;

    int set_size = cci_set_size(in_set);

    MAKE_STD_ZVAL(tmp_ptr);
    array_init(tmp_ptr);

    for (i = 0; i < set_size; i++) {
	res = cci_set_get(in_set, i + 1, CCI_A_TYPE_STR, &buffer, &ind);
	if (res < 0) {
	    cubrid_array_destroy(HASH_OF(tmp_ptr) ZEND_FILE_LINE_CC);
	    FREE_ZVAL(tmp_ptr);
	    return res;
	}

	if (ind < 0) {
	    add_index_unset(tmp_ptr, i);
	} else {
	    add_index_string(tmp_ptr, i, buffer, 1);
	}
    }

    res = zend_hash_index_update(HASH_OF(arg), index, (void *) &tmp_ptr, sizeof(zval *), NULL);
    if (res == FAILURE) {
	cubrid_array_destroy(HASH_OF(tmp_ptr) ZEND_FILE_LINE_CC);
	FREE_ZVAL(tmp_ptr);
	return CUBRID_ER_PHP;
    }

    return 0;
}

static int cubrid_add_assoc_array(zval *arg, char *key, T_CCI_SET in_set TSRMLS_DC)
{
    zval *tmp_ptr;

    int i;
    int ind;
    char *buffer;
    int cubrid_retval = 0;

    int set_size = cci_set_size(in_set);

    MAKE_STD_ZVAL(tmp_ptr);
    array_init(tmp_ptr);

    for (i = 0; i < set_size; i++) {
	if ((cubrid_retval = cci_set_get(in_set, i + 1, CCI_A_TYPE_STR, &buffer, &ind)) < 0) {
	    cubrid_array_destroy(HASH_OF(tmp_ptr) ZEND_FILE_LINE_CC);
	    FREE_ZVAL(tmp_ptr);
	    return cubrid_retval;
	}

	if (ind < 0) {
	    add_index_unset(tmp_ptr, i);
	} else {
	    add_index_string(tmp_ptr, i, buffer, 1);
	}
    }

    if ((cubrid_retval = zend_hash_update(HASH_OF(arg), key, strlen(key) + 1, 
		    (void *) &tmp_ptr, sizeof(zval *), NULL)) == FAILURE) {
	cubrid_array_destroy(HASH_OF(tmp_ptr) ZEND_FILE_LINE_CC);
	FREE_ZVAL(tmp_ptr);
	return CUBRID_ER_PHP;
    }

    return 0;
}

static int cubrid_array_destroy(HashTable * ht ZEND_FILE_LINE_DC)
{
    zend_hash_destroy(ht);
    FREE_HASHTABLE_REL(ht);
    return SUCCESS;
}

static int cubrid_make_set(HashTable *ht, T_CCI_SET *set)
{
    void **set_array = NULL;
    int *set_null = NULL;
    char *key;
    ulong index;
    zval **data;

    int set_size;
    int i;
    int error_code;
    int cubrid_retval = 0;

    set_size = zend_hash_num_elements(ht);
    set_array = (void **) safe_emalloc(set_size, sizeof(void *), 0);

    for (i = 0; i < set_size; i++) {
	set_array[i] = NULL;
    }

    set_null = (int *) safe_emalloc(set_size, sizeof(int), 0);

    zend_hash_internal_pointer_reset(ht);
    for (i = 0; i < set_size; i++) {
	if (zend_hash_get_current_key(ht, &key, &index, 0) == HASH_KEY_NON_EXISTANT) {
	    break;
	}

	zend_hash_get_current_data(ht, (void **) &data);
	switch (Z_TYPE_PP(data)) {
	case IS_NULL:
	    set_array[i] = NULL;
	    set_null[i] = 1;

	    break;
	case IS_LONG:
	case IS_DOUBLE:
	    convert_to_string_ex(data);
	case IS_STRING:
	    set_array[i] = Z_STRVAL_PP(data);
	    set_null[i] = 0;

	    break;
	default:
	    error_code = CUBRID_ER_NOT_SUPPORTED_TYPE;
	    goto ERR_CUBRID_MAKE_SET;
	}

	zend_hash_move_forward(ht);
    }

    if ((cubrid_retval = cci_set_make(set, CCI_U_TYPE_STRING, set_size, set_array, set_null)) < 0) {
	*set = NULL;
	error_code = cubrid_retval;
	goto ERR_CUBRID_MAKE_SET;
    }

    efree(set_array);
    efree(set_null);

    return 0;

ERR_CUBRID_MAKE_SET:

    if (set_array) {
	efree(set_array);
    }

    if (set_null) {
	efree(set_null);
    }

    return error_code;
}

static int type2str(T_CCI_COL_INFO * column_info, char *type_name, int type_name_len)
{
    char buf[64];

    switch (CCI_GET_COLLECTION_DOMAIN(column_info->type)) {
    case CCI_U_TYPE_UNKNOWN:
	snprintf(buf, sizeof(buf), "unknown");
	break;
    case CCI_U_TYPE_CHAR:
	snprintf(buf, sizeof(buf), "char(%d)", column_info->precision);
	break;
    case CCI_U_TYPE_STRING:
	snprintf(buf, sizeof(buf), "varchar(%d)", column_info->precision);
	break;
    case CCI_U_TYPE_NCHAR:
	snprintf(buf, sizeof(buf), "nchar(%d)", column_info->precision);
	break;
    case CCI_U_TYPE_VARNCHAR:
	snprintf(buf, sizeof(buf), "varnchar(%d)", column_info->precision);
	break;
    case CCI_U_TYPE_BIT:
	snprintf(buf, sizeof(buf), "bit");
	break;
    case CCI_U_TYPE_VARBIT:
	snprintf(buf, sizeof(buf), "varbit(%d)", column_info->precision);
	break;
    case CCI_U_TYPE_NUMERIC:
	snprintf(buf, sizeof(buf), "numeric(%d,%d)", column_info->precision, column_info->scale);
	break;
    case CCI_U_TYPE_INT:
	snprintf(buf, sizeof(buf), "integer");
	break;
    case CCI_U_TYPE_SHORT:
	snprintf(buf, sizeof(buf), "smallint");
	break;
    case CCI_U_TYPE_MONETARY:
	snprintf(buf, sizeof(buf), "monetary");
	break;
    case CCI_U_TYPE_FLOAT:
	snprintf(buf, sizeof(buf), "float");
	break;
    case CCI_U_TYPE_DOUBLE:
	snprintf(buf, sizeof(buf), "double");
	break;
    case CCI_U_TYPE_DATE:
	snprintf(buf, sizeof(buf), "date");
	break;
    case CCI_U_TYPE_TIME:
	snprintf(buf, sizeof(buf), "time");
	break;
    case CCI_U_TYPE_TIMESTAMP:
	snprintf(buf, sizeof(buf), "timestamp");
	break;
    case CCI_U_TYPE_SET:
	snprintf(buf, sizeof(buf), "set");
	break;
    case CCI_U_TYPE_MULTISET:
	snprintf(buf, sizeof(buf), "multiset");
	break;
    case CCI_U_TYPE_SEQUENCE:
	snprintf(buf, sizeof(buf), "sequence");
	break;
    case CCI_U_TYPE_OBJECT:
	snprintf(buf, sizeof(buf), "object");
	break;
    case CCI_U_TYPE_BIGINT:
	snprintf(buf, sizeof(buf), "bigint");
	break;
    case CCI_U_TYPE_DATETIME:
	snprintf(buf, sizeof(buf), "datetime");
	break;
    default:
	/* should not enter here */
	snprintf(buf, sizeof(buf), "[unknown]");
	return -1;
    }

    if (CCI_IS_SET_TYPE(column_info->type)) {
	snprintf(type_name, type_name_len, "set(%s)", buf);
    } else if (CCI_IS_MULTISET_TYPE(column_info->type)) {
	snprintf(type_name, type_name_len, "multiset(%s)", buf);
    } else if (CCI_IS_SEQUENCE_TYPE(column_info->type)) {
	snprintf(type_name, type_name_len, "sequence(%s)", buf);
    } else {
	snprintf(type_name, type_name_len, "%s", buf);
    }

    return 0;
}

static int numeric_type(T_CCI_U_TYPE type)
{
    if (type == CCI_U_TYPE_NUMERIC || 
	type == CCI_U_TYPE_INT ||
	type == CCI_U_TYPE_SHORT || 
	type == CCI_U_TYPE_FLOAT ||
	type == CCI_U_TYPE_DOUBLE || 
	type == CCI_U_TYPE_BIGINT ||
	type == CCI_U_TYPE_MONETARY) {
	return 1;
    } else {
	return 0;
    }
}

static int get_cubrid_u_type_by_name(const char *type_name)
{
    int i;
    int size = sizeof(db_type_info) / sizeof(db_type_info[0]);

    for (i = 0; i < size; i++) {
	if (strcasecmp(type_name, db_type_info[i].type_name) == 0) {
	    return db_type_info[i].cubrid_u_type;
	}
    }

    return CCI_U_TYPE_UNKNOWN;
}

static int get_cubrid_u_type_len(T_CCI_U_TYPE type)
{
    int i;
    int size = sizeof(db_type_info) / sizeof(db_type_info[0]);
    DB_TYPE_INFO type_info;

    for (i = 0; i < size; i++) {
	type_info = db_type_info[i];
	if (type == type_info.cubrid_u_type) {
	    return type_info.len;
	}
    }

    return 0;
}

static long get_last_autoincrement(char *class_name, char **columns, char **values, int conn_handle)
{
    char sql_stmt[256];
    T_CCI_ERROR error;

    int cubrid_retval = 0;
    int request_handle;

    char *buf_col, *buf_val;
    int ind_col, ind_val;
    int i;

    if (!conn_handle) {
	return -1;
    }

    snprintf(sql_stmt, sizeof(sql_stmt), 
	    "select att_name, current_val from db_serial where class_name='%s' and started=1", class_name);

    if ((cubrid_retval = cci_prepare(conn_handle, sql_stmt, 0, &error)) < 0) {
	handle_error(cubrid_retval, &error);
	return -1;
    }

    request_handle = cubrid_retval;

    if ((cubrid_retval = cci_execute(request_handle, CCI_EXEC_ASYNC, 0, &error)) < 0) {
	handle_error(cubrid_retval, &error);
        goto ERR_GET_LAST_AUTOINCREMENT;
    }

    i = 0;
    while (1) {
	cubrid_retval = cci_cursor(request_handle, 1, CCI_CURSOR_CURRENT, &error);
	if (cubrid_retval < 0 && cubrid_retval != CCI_ER_NO_MORE_DATA) {
	    handle_error(cubrid_retval, &error);
            goto ERR_GET_LAST_AUTOINCREMENT;
	}

	if (cubrid_retval == CCI_ER_NO_MORE_DATA) {
	    break;
	}

	if ((cubrid_retval = cci_fetch(request_handle, &error)) < 0) {
	    handle_error(cubrid_retval, &error);
            goto ERR_GET_LAST_AUTOINCREMENT;
	}

	if ((cubrid_retval = cci_get_data(request_handle, 1, CCI_A_TYPE_STR, &buf_col, &ind_col)) < 0) {
	    handle_error(cubrid_retval, &error);
            goto ERR_GET_LAST_AUTOINCREMENT;
	}

	if ((cubrid_retval = cci_get_data(request_handle, 2, CCI_A_TYPE_STR, &buf_val, &ind_val)) < 0) {
	    handle_error(cubrid_retval, &error);
            goto ERR_GET_LAST_AUTOINCREMENT;
	}

	if (ind_col != -1) {
	    columns[i] = (char *) safe_emalloc(ind_col + 1, sizeof(char *), 0);
	    values[i] = (char *) safe_emalloc(MAX_SERIAL_PRECISION + 1, sizeof(char *), 0);

	    strlcpy(columns[i], buf_col, ind_col + 1);
	    strlcpy(values[i], buf_val, MAX_SERIAL_PRECISION + 1);
	} else {
	    columns[i] = NULL;
	    values[i] = NULL;
	}

	i++;
    }

    if ((cubrid_retval = cci_close_req_handle(request_handle)) < 0) {
	handle_error(cubrid_retval, NULL);
	return -1;
    }

    return i;

ERR_GET_LAST_AUTOINCREMENT:

    cci_close_req_handle(request_handle);
    return -1;
}
