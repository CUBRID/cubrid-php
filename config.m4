dnl config.m4, 2013/03/19 18:15:36 lijinhu
dnl config.m4 for extension cubrid
dnl don't forget to call PHP_EXTENSION(cubrid)

dnl If your extension references something external, use with:

PHP_ARG_WITH(cubrid, for CUBRID support,
[  --with-cubrid           Include CUBRID support.], yes)

if test "$PHP_CUBRID" != "no"; then

    cubrid_dir=`dirname $0`
    CUBRID_INCDIR=""
    CUBRID_LIBDIR=""
    BROKER_INCDIR=""

    case $host in
      *-linux-*) os=linux ;;
    esac
    
    case $host in
      *-apple-*) os=mac ;
    esac
    
    if test "$os" = "linux"; then
        CUBRID_INCDIR="$cubrid_dir/cci-src/src/cci"
     	BROKER_INCDIR="$cubrid_dir/cci-src/src/broker"
        CUBRID_LIBDIR="$cubrid_dir/cci-src/cci/.libs"
        CCISRC_DIR="$cubrid_dir/cci-src"
        tar xvjf cci-src.tar.bz2
        AC_CHECK_SIZEOF([int *])

        if test "$ac_cv_sizeof_int_p" = "8"; then
    	    AC_MSG_NOTICE([Build static cci lib 64 bits])
            pushd $CCISRC_DIR
            chmod +x configure
            chmod +x external/libregex38a/configure
            chmod +x external/libregex38a/install-sh
            ./configure --enable-64bit
    	    make
            popd
        else
    	    AC_MSG_NOTICE([Build static cci lib])
            pushd $CCISRC_DIR
            chmod +x configure
            chmod +x external/libregex38a/configure
            chmod +x external/libregex38a/install-sh
            ./configure
    	    make
            popd
        fi
    elif test "$os" = "mac"; then
        CUBRID_INCDIR="$cubrid_dir/cci-src/src/cci"
     	BROKER_INCDIR="$cubrid_dir/cci-src/src/broker"
        CUBRID_LIBDIR="$cubrid_dir/cci-src/cci/.libs"
        CCISRC_DIR="$cubrid_dir/cci-src"
        tar xvjf cci-src.tar.bz2
        AC_CHECK_SIZEOF([int *])

        if test "$ac_cv_sizeof_int_p" = "8"; then
    	    AC_MSG_NOTICE([Build static cci lib 64 bits])
            pushd $CCISRC_DIR
            chmod +x configure
            chmod +x external/libregex38a/configure
            chmod +x external/libregex38a/install-sh
            ./configure --enable-64bit
    	    make
            popd
        else
    	    AC_MSG_NOTICE([Build static cci lib])
            pushd $CCISRC_DIR
            chmod +x configure
            chmod +x external/libregex38a/configure
            chmod +x external/libregex38a/install-sh
            ./configure
    	    make
            popd
        fi     
    else
        AC_MSG_ERROR([Your OS not supported. Exit.])
    fi

    if ! test -r "$CUBRID_INCDIR/cas_cci.h"; then
        AC_MSG_ERROR([$cubrid_dir/$CUBRID_INCDIR/cas_cci.h not found. This package must be broken. Please report a bug.])
    fi

    if test -r "$CUBRID_LIBDIR/libcascci.a"; then
        #
        # libcascci.a depends on pthread and stdc++.
        #

        PHP_CHECK_LIBRARY("pthread", pthread_create, [], [
        AC_MSG_ERROR([pthread library not found. Please install it at first.])
        ], [])

        PHP_CHECK_LIBRARY("stdc++", main, [], [
        AC_MSG_ERROR([stdc++ library not found! Please install it at first.])
        ], [])

    else
        AC_MSG_ERROR([libcascci.a not found. Failed to build cci lib. Exit.])
    fi

    dnl Action..
    PHP_ADD_INCLUDE("$CUBRID_INCDIR")
    PHP_ADD_INCLUDE("$BROKER_INCDIR")

    PHP_ADD_LIBRARY(stdc++,,CUBRID_SHARED_LIBADD)
    PHP_ADD_LIBRARY(pthread,,CUBRID_SHARED_LIBADD)
    LDFLAGS="$LDFLAGS $CUBRID_LIBDIR/libcascci.a -lpthread"

    PHP_SUBST(CUBRID_SHARED_LIBADD)
    PHP_NEW_EXTENSION(cubrid, php_cubrid.c, $ext_shared)
fi
