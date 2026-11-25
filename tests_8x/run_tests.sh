#!/bin/bash
#
# Run the tests with the extension built by ../build.sh <ver>.
#
#   ./run_tests.sh [--php-bin PATH] [run-tests.php options] [tests...]
#       no test given: all tests
#   ./run_tests.sh [--php-bin PATH] --php script.php [args...]
#       run a PHP script with the extension loaded
#
# Options
#   --php-bin PATH, --php-bin=PATH
#                PHP binary to use (overrides PHP_BIN), e.g. ~/php/8.4/bin/php
#
# Environment
#   PHP_BIN      PHP binary        (default: php in PATH), e.g. ~/php/8.4/bin/php
#   CUBRID_EXT   extension to test (default: ../build_linux/<ver>/modules/cubrid.so,
#                <ver> = 83, 84, 85 from PHP_BIN)

# Parse --php-bin before cd so that relative paths are resolved from the caller's directory.
case "$1" in
    --php-bin)
        [ -n "$2" ] || { echo "[ERROR] --php-bin requires a path" >&2; exit 1; }
        PHP_BIN=$2; shift 2 ;;
    --php-bin=*)
        PHP_BIN=${1#--php-bin=}; shift ;;
esac
if [ -n "$PHP_BIN" ] && [[ "$PHP_BIN" == */* ]]; then
    PHP_BIN=$(readlink -m "$PHP_BIN")
fi

cd "$(dirname "$0")"

PHP_BIN_ARG=${PHP_BIN:-php}
PHP_BIN=$(command -v "$PHP_BIN_ARG") || { echo "[ERROR] php not found: $PHP_BIN_ARG" >&2; exit 1; }
php_ver=$("$PHP_BIN" -n -r 'echo PHP_MAJOR_VERSION, PHP_MINOR_VERSION;')
CUBRID_EXT=${CUBRID_EXT:-../build_linux/$php_ver/modules/cubrid.so}

if [ ! -f "$CUBRID_EXT" ]; then
    echo "[ERROR] $CUBRID_EXT not found. Run '../build.sh $php_ver' first." >&2
    exit 1
fi
CUBRID_EXT=$(readlink -f "$CUBRID_EXT")

if [ "$1" = "--php" ]; then
    shift
    exec "$PHP_BIN" -n -d extension="$CUBRID_EXT" "$@"
fi

# Run the default test set when no test path is given.
has_target=0
for arg in "$@"; do
    [ -e "$arg" ] && has_target=1
done
targets=(.)
[ $has_target -eq 1 ] && targets=()

export NO_COLOR=1
exec "$PHP_BIN" run-tests.php -q -n -d extension="$CUBRID_EXT" -p "$PHP_BIN" "$@" "${targets[@]}" | tee php_tests_linux_${php_ver}.log
