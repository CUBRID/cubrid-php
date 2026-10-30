# Running tests_8x (Linux)

`run_tests.sh` runs the `.phpt` tests with `run-tests.php`, loading the `cubrid.so`
built by `../build.sh <ver>`. The system `php.ini` is not read (`-n`); only the
extension is loaded with `-d extension=...`, so any PHP binary can be tested the
same way.

## 1. Prerequisites

### CUBRID server
- A broker must be listening on port `33000` with `demodb` running.
- Connection settings are in `connect.inc` (`demodb`, user `public`, empty password).
- The default host is `test-db-server`. For a local server, set
  `CUBRID_TEST_HOST=localhost`.

### Build the extension
Build for the PHP version you want to test first. The result is
`../build_linux/<ver>/modules/cubrid.so`.

```bash
cd ..
./build.sh 83      # uses ~/php/8.3/bin/php-config -> build_linux/83/modules/cubrid.so
./build.sh 84      # uses ~/php/8.4/bin/php-config -> build_linux/84/modules/cubrid.so
./build.sh 85      # uses php-config in PATH (8.5) -> build_linux/85/modules/cubrid.so
```

By default `build.sh` looks for `~/php/<x.y>/bin/php-config` (the base directory can
be changed with `PHP_HOME`), and falls back to `php-config` in `PATH` if its version
matches. For a PHP installed elsewhere, pass `--php-config PATH`.

## 2. Selecting the PHP version

`run_tests.sh` picks the PHP binary from the `PHP_BIN` environment variable.

| Variable | Meaning | Default |
|---|---|---|
| `PHP_BIN` | PHP binary used for the tests | `php` in `PATH` (currently `/usr/bin/php`, 8.5) |
| `CUBRID_EXT` | Extension to load | `../build_linux/<ver>/modules/cubrid.so` (`<ver>` is detected from `PHP_BIN`: 83, 84, 85) |
| `CUBRID_TEST_HOST` | CUBRID broker host | `test-db-server` |

Changing `PHP_BIN` is enough: the `cubrid.so` from the matching `build_linux/<ver>` is
selected automatically. If that build does not exist, the script stops with a
message such as
`[ERROR] ../build_linux/84/modules/cubrid.so not found. Run '../build.sh 84' first.`

## 3. Examples

```bash
cd tests_8x

# All tests with the default PHP (php in PATH = 8.5)
CUBRID_TEST_HOST=localhost ./run_tests.sh

# All tests with PHP 8.4
CUBRID_TEST_HOST=localhost PHP_BIN=~/php/8.4/bin/php ./run_tests.sh

# All tests with PHP 8.3
CUBRID_TEST_HOST=localhost PHP_BIN=~/php/8.3/bin/php ./run_tests.sh

# Selected tests only
CUBRID_TEST_HOST=localhost PHP_BIN=~/php/8.4/bin/php ./run_tests.sh cubrid_connect.phpt cubrid_fetch.phpt

# Export once and run several times
export CUBRID_TEST_HOST=localhost
export PHP_BIN=~/php/8.3/bin/php
./run_tests.sh
./run_tests.sh cubrid_lob2_bind.phpt
```

### Running several versions in a row

```bash
export CUBRID_TEST_HOST=localhost
for php in ~/php/8.3/bin/php ~/php/8.4/bin/php /usr/bin/php; do
    echo "===== $php"
    PHP_BIN=$php ./run_tests.sh | grep -E '^(PHP_VERSION|FAIL|Tests (passed|failed))'
done
```

### Passing options to run-tests.php
Arguments other than test paths are passed through to `run-tests.php`.

```bash
PHP_BIN=~/php/8.4/bin/php ./run_tests.sh --show-diff cubrid_query.phpt   # print diff on failure
PHP_BIN=~/php/8.4/bin/php ./run_tests.sh -j4                             # run in parallel
```

### Specifying the extension explicitly

```bash
PHP_BIN=~/php/8.4/bin/php CUBRID_EXT=/path/to/cubrid.so ./run_tests.sh
```

### Running a PHP script with the extension loaded (`--php`)
Use this to run an arbitrary script or one-liner instead of `.phpt` tests.

```bash
PHP_BIN=~/php/8.3/bin/php ./run_tests.sh --php -r 'echo PHP_VERSION, " ", cubrid_version(), "\n";'
# 8.3.36 11.3.0.0001

CUBRID_TEST_HOST=localhost PHP_BIN=~/php/8.4/bin/php ./run_tests.sh --php my_script.php
```

## 4. Checking results

- The `PHP :` / `PHP_VERSION :` lines at the top of the output show which PHP was used.
- For each failed test, `<name>.diff`, `.out`, `.exp` and `.log` files are created in
  this directory. To clean them up before the next run:

```bash
for t in *.phpt; do b=${t%.phpt}; rm -f "$b".{diff,out,exp,log,php,sh}; done
```

  (Files are removed by `.phpt` name only, so repository files such as `run-tests.php`,
  `until.php` and `run_tests.sh` are kept. Do not delete with `*.php` or `*.sh` globs.)

## 5. Notes

- `run_tests.sh` runs PHP with `-n`, so an `extension=cubrid.so` entry in `php.ini`
  (added by `build.sh --install`) has no effect on the tests.
- PHP versions older than 8.3 are not supported (`build.sh` accepts only 83, 84, 85).
- On Windows, use `run_tests.bat` (e.g. `run_tests.bat 84`, `run_tests.bat 85-ts`; see the
  comments at the top of the file).
