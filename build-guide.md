# CUBRID PHP Driver Build Guide

Source: <https://github.com/CUBRID/cubrid-php>

1. [Overview](#1-overview)
2. [Building on Linux](#2-building-on-linux)
3. [Building on Windows](#3-building-on-windows)
4. [Installing with PIE](#4-installing-with-pie)

## 1. Overview

CUBRID PHP Driver (`cubrid`) is the official PHP extension (`cubrid_*` functions) for the CUBRID database. The CUBRID CCI library is included as the `cci-src` git submodule and is built together with the driver, so CUBRID does not need to be installed to build the driver.

| Platform | PHP | Compiler | Module |
|---|---|---|---|
| Linux x86_64 | 8.3, 8.4, 8.5 (NTS, ZTS) | GCC | `cubrid.so` (CCI and OpenSSL linked statically) |
| Windows x64 | 8.4, 8.5 (NTS, TS) | Visual Studio 2022 (vs17) | `php_cubrid.dll` + `cascci.dll` |

32-bit platforms and ARM64 are not supported. PHP 8.2 and earlier are not supported.

Most users do not need to build the driver: [4. Installing with PIE](#4-installing-with-pie) downloads, builds (Linux) and enables it in one command. See also the CUBRID manual: <https://www.cubrid.org/manual/en/11.4/api/php.html>.

## 2. Building on Linux

### 2.1 Requirements

| Item | Note |
|---|---|
| GCC / G++, make | GCC 8 or later is recommended (e.g. `gcc-toolset` / `devtoolset` on RHEL-compatible systems). |
| CMake | Used to build CCI (3.x recommended). |
| autoconf | Required by `phpize`. |
| PHP development files | `phpize` and `php-config` of the target PHP, e.g. `php-devel` (RHEL) or `php8.x-dev` (Debian/Ubuntu). |
| git | Only for building from the git repository. The release archive does not need git. |

Example (RHEL-compatible): `dnf install gcc gcc-c++ make cmake autoconf php-devel`\
Example (Debian/Ubuntu): `apt install build-essential cmake autoconf php8.4-dev`

### 2.2 Getting the source

From the git repository (the `cci-src` submodule is required):

```
git clone --recursive https://github.com/CUBRID/cubrid-php.git
cd cubrid-php
# for an existing clone
git submodule update --init
```

Or download the source release archive `php_cubrid-<version>-src.tgz` from [GitHub Releases](https://github.com/CUBRID/cubrid-php/releases), which already contains `cci-src`:

```
sha256sum -c php_cubrid-<version>-src.tgz.sha256
tar xzf php_cubrid-<version>-src.tgz
cd php_cubrid-<version>-src
```

### 2.3 Building with build.sh

`build.sh` builds the driver out of the source tree in `build_linux/<ver>/`, so the source tree stays clean and several PHP versions can be built side by side. CCI is built once and shared.

```
./build.sh <ver> [--php-config PATH] [--install] [--clean]
    ver: 83, 84, 85

./build.sh 84 --php-config /usr/bin/php-config      # -> build_linux/84/modules/cubrid.so
./build.sh 85 --install                             # also install it and enable it in php.ini
```

| Option | Description |
|---|---|
| `--php-config PATH` | `php-config` of the target PHP. Default: `$PHP_HOME/<x.y>/bin/php-config` (`PHP_HOME=~/php`), otherwise `php-config` in `PATH` if its version matches. |
| `--install` | Run `make install` and add `extension=cubrid.so` to the `php.ini` of the target PHP. |
| `--clean` | Remove the build directory and build from scratch. |

### 2.4 Building with phpize

The standard PHP extension build also works (in the source tree):

```
phpize
./configure --with-php-config=/usr/bin/php-config
make
sudo make install
```

The extension is enabled by default, so `--with-cubrid` is not needed. `configure` builds CCI (`cci-src/build_x86_64_release`) first, which takes a few minutes the first time.

### 2.5 Enabling the extension

Add the following to `php.ini`, or to a file in the additional ini directory (e.g. `/etc/php.d/cubrid.ini` or `/etc/php/8.4/mods-available/cubrid.ini`):

```
extension=cubrid
```

Check that it is loaded:

```
php -m | grep -i cubrid
php --ri cubrid
```

Restart the web server or PHP-FPM after changing the configuration.

## 3. Building on Windows

### 3.1 Requirements

- Visual Studio 2022 with the "Desktop development with C++" workload (MSVC v143, CMake)
- Git for Windows (the CCI CMake build uses `git` and `awk` of `Git\usr\bin`)
- Windows PowerShell 5.1 or PowerShell 7

The PHP binaries, the PHP devel packs and the PHP SDK binary tools are downloaded automatically by `build_win.ps1` into `build_win\downloads`, unless existing ones are given with `-PhpDir`, `-DevelPack` and `-PhpSdk`.

### 3.2 Building with build_win.ps1

```
.\build_win.ps1 <ver> [-ThreadSafe] [-PhpVersion X.Y.Z] [-PhpDir DIR] [-DevelPack DIR] [-PhpSdk DIR] [-Clean]
    ver: 84, 85

.\build_win.ps1 84                      # PHP 8.4 NTS -> build_win\84-nts\modules\php_cubrid.dll
.\build_win.ps1 85 -ThreadSafe          # PHP 8.5 TS  -> build_win\85-ts\modules\php_cubrid.dll
.\build_win.ps1 84 -PhpVersion 8.4.14 -Clean
```

CCI is built once with CMake as `cascci.dll` (`build_win\cci`) and shared by all targets. `build_win\<ver>-<nts|ts>\modules` contains `php_cubrid.dll` and `cascci.dll`. Run `Get-Help .\build_win.ps1 -Detailed` for all options.

### 3.3 Installing the build

1. Copy `php_cubrid.dll` to the `ext` directory of PHP (e.g. `C:\php\ext`).
2. Copy `cascci.dll` next to `php.exe` (e.g. `C:\php`) or to a directory in `PATH`. Windows does not look for dependent DLLs in the `ext` directory.
3. Add `extension=cubrid` to `php.ini`.
4. The Microsoft Visual C++ 2015-2022 Redistributable (x64) is required; it is installed with the official PHP builds in most cases.

## 4. Installing with PIE

[PIE](https://github.com/php/pie) (PHP Installer for Extensions) installs the driver from [GitHub Releases](https://github.com/CUBRID/cubrid-php/releases) and enables it in `php.ini`. On Linux it downloads the source archive (`php_cubrid-<version>-src.tgz`) and builds it; on Windows it downloads the prebuilt binary that matches the PHP version and thread safety (NTS/TS).

### 4.1 Installing PIE

Linux (PIE needs PHP 8.1 or later with the `openssl` and `zip` extensions):

```
curl -fL -o pie.phar https://github.com/php/pie/releases/latest/download/pie.phar
sudo install -m 755 pie.phar /usr/local/bin/pie
```

Windows (PowerShell): the PIE executable contains its own PHP, so no `php.ini` changes are needed.

```
Invoke-WebRequest -Uri https://github.com/php/pie/releases/latest/download/pie-Windows-X64.exe -OutFile pie.exe
```

To use `pie.phar` on Windows instead (`php pie.phar ...`), the PHP that runs it needs `openssl` and `zip`. The windows.php.net packages have no `php.ini`, so create one from `php.ini-development` and set:

```
extension_dir = "C:\php\ext"     ; absolute path: a relative "ext" is resolved from the current directory
extension=openssl
extension=zip
```

### 4.2 Adding the CUBRID repository

The driver is installed from the official GitHub repository <https://github.com/CUBRID/cubrid-php>. Add it to PIE once before the first install:

```
# Linux
pie repository:add vcs https://github.com/CUBRID/cubrid-php
pie repository:add --with-php-config=/path/to/php-config vcs https://github.com/CUBRID/cubrid-php    # another PHP installation

# Windows
.\pie.exe repository:add --with-php-path=C:\php\php.exe vcs https://github.com/CUBRID/cubrid-php

# check
pie repository:list
```

PIE keeps the repositories per PHP installation, so add the repository for each PHP installation that the driver is installed into, with the same `--with-php-config` / `--with-php-path` option as the install command.

### 4.3 Installing on Linux

The driver is built from source, so the build tools of [2.1 Requirements](#21-requirements) are needed (git is not).

```
pie install cubrid/cubrid                                  # the latest version
pie install cubrid/cubrid:<version>                        # a specific version
pie install --with-php-config=/path/to/php-config cubrid/cubrid     # another PHP installation
php --ri cubrid
```

- Run `pie` **without** `sudo`; PIE asks for the password itself when it installs the module.
- PIE enables the extension in an ini file of the target PHP, or shows a warning when it cannot; then add `extension=cubrid` by hand as described in [2.5 Enabling the extension](#25-enabling-the-extension).
- `php --ri cubrid` shows `Driver Version => <version>`.
- Restart the web server or PHP-FPM after installing.

Without network access, download `php_cubrid-<version>-src.tgz` from GitHub Releases on another machine and install the extracted archive:

```
tar xzf php_cubrid-<version>-src.tgz
pie repository:add path $PWD/php_cubrid-<version>-src
pie install cubrid/cubrid:*@dev
```

### 4.4 Installing on Windows

```
.\pie.exe install --with-php-path=C:\php\php.exe cubrid/cubrid
.\pie.exe install --with-php-path=C:\php\php.exe cubrid/cubrid:<version>
C:\php\php.exe --ri cubrid
```

- Run PowerShell as administrator when PHP is in a protected directory such as `C:\Program Files`, because PIE writes into the PHP directory.
- PIE selects the zip for the PHP version and thread safety of the target PHP. A windows.php.net package whose directory name does not contain `nts` (e.g. `php-8.5.8-Win32-vs17-x64`) is a TS build.
- Installed files: `ext\php_cubrid.dll`, `cascci.dll` next to `php.exe`, `extras\cubrid\LICENSE`, and `extension=cubrid` in `php.ini`.
- If the module does not load, check that `cascci.dll` is next to `php.exe` and that the Microsoft Visual C++ 2015-2022 Redistributable (x64) is installed.

### 4.5 Uninstalling

```
pie uninstall cubrid/cubrid
pie repository:remove https://github.com/CUBRID/cubrid-php         # when the repository is not needed any more
```

PIE disables the extension and removes the module. On Windows, `cascci.dll` next to `php.exe` may have to be removed by hand when no other CUBRID driver uses it.

### 4.6 Troubleshooting

| Message | Cause and solution |
|---|---|
| `Unable to find an installable package cubrid/cubrid ...` | The CUBRID repository is not added for this PHP installation ([4.2](#42-adding-the-cubrid-repository)), or no release supports this PHP version. |
| `Could not find release asset for cubrid/cubrid named one of "..."` / `Windows archive with prebuilt extension ... was not attached on release ...` | No package for this PHP version or thread safety. Check the supported platforms in [1. Overview](#1-overview). |
| `PIE requires the "openssl" extension, but it is not loaded` | Enable `openssl` (and `zip`) for the PHP that runs `pie.phar`, or use `pie-Windows-X64.exe`. |
| `Unable to load dynamic library 'openssl' (tried: C:\php\ext\...)` | `extension_dir` is not set, so the built-in default `C:\php\ext` is used. Set an absolute `extension_dir`. |
