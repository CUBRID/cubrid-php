CUBRID PHP Driver
=================

The official PHP extension (`cubrid_*` functions) for the [CUBRID](https://www.cubrid.org) database.
The CUBRID CCI library is included (`cci-src` submodule) and built together with the driver,
so CUBRID does not need to be installed on the client.

Supported platforms
-------------------

| Platform | PHP | Package |
|---|---|---|
| Linux x86_64 | 8.3, 8.4, 8.5 | `php_cubrid-<version>-src.tgz` (built on install) |
| Windows x64 | 8.4, 8.5 (NTS, TS) | `php_cubrid-<version>-<8.4\|8.5>-<nts\|ts>-vs17-x86_64.zip` |

PHP 8.2 and earlier are not supported by this version.
The packages are published on [GitHub Releases](https://github.com/CUBRID/cubrid-php/releases)
with a `.sha256` checksum file each.

Installation
------------

### With PIE (Linux, Windows)

[PIE](https://github.com/php/pie) downloads, builds (Linux) and enables the extension.
Run `pie` without `sudo`; on Windows use `pie.exe` with `--with-php-path=C:\path\to\php.exe`.
See [build-guide.md](build-guide.md#4-installing-with-pie) for details.

```
pie repository:add vcs https://github.com/CUBRID/cubrid-php   # once per PHP installation
pie install cubrid/cubrid
pie install cubrid/cubrid:<version>     # a specific version
```

On Linux the extension is built from source, so a compiler, CMake, autoconf and the PHP
development files (`phpize`) are required, e.g. `dnf install gcc gcc-c++ make cmake autoconf php-devel`.

### From the source package (Linux)

```
sha256sum -c php_cubrid-<version>-src.tgz.sha256
tar xzf php_cubrid-<version>-src.tgz
cd php_cubrid-<version>-src
phpize
./configure --with-php-config=/usr/bin/php-config
make
sudo make install
```

Then add `extension=cubrid` to `php.ini` (or `/etc/php.d/cubrid.ini`) and check with `php --ri cubrid`.
Without network access, the extracted package can also be installed with PIE:
`pie repository:add path /path/to/php_cubrid-<version>-src` and `pie install cubrid/cubrid:*@dev`.

### From the binary package (Windows)

1. Extract the zip that matches the PHP version and thread safety (`php -v`: NTS or ZTS/TS).
2. Copy `php_cubrid-<...>.dll` to the PHP `ext` directory as `php_cubrid.dll`.
3. Copy `cascci.dll` next to `php.exe` (or to a directory in `PATH`).
4. Add `extension=cubrid` to `php.ini`.

The Microsoft Visual C++ 2015-2022 Redistributable (x64) is required.

Building
--------

```
git clone --recursive https://github.com/CUBRID/cubrid-php.git
cd cubrid-php
./build.sh 84            # Linux, PHP 8.3/8.4/8.5 -> build_linux/84/modules/cubrid.so
.\build_win.ps1 84       # Windows, PHP 8.4/8.5  -> build_win\84-nts\modules\php_cubrid.dll
```

See [build-guide.md](build-guide.md) for the requirements, tests and how to make a release
(`script/release.sh`, `script/release.ps1`).

Documentation
-------------

- CUBRID PHP driver manual: https://www.cubrid.org/manual/en/11.4/api/php.html
- CUBRID APIs: https://www.cubrid.org/manual/en/11.4/api/index.html
- Release notes: [release-notes.html](release-notes.html)

License
-------

The driver and CCI are distributed under the BSD 3-Clause License (the CUBRID database server
engine is under the Apache License 2.0). The binaries include OpenSSL. See [LICENSE](LICENSE).
