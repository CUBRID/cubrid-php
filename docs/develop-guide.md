# CUBRID PHP Driver Build Guide

Source: <https://github.com/CUBRID/cubrid-php>

1. [Overview](#1-overview)
2. [Building on Linux](#2-building-on-linux)
3. [Building on Windows](#3-building-on-windows)
4. [Testing](#4-testing)
5. [Making a release](#5-making-a-release)
6. [Testing a release with PIE](#6-testing-a-release-with-pie)

## 1. Overview

CUBRID PHP Driver (`cubrid`) is the official PHP extension (`cubrid_*` functions) for the CUBRID database. The CUBRID CCI library is included as the `cci-src` git submodule and is built together with the driver, so CUBRID does not need to be installed to build the driver.

| Platform | PHP | Compiler | Module |
|---|---|---|---|
| Linux x86_64 | 8.3, 8.4, 8.5 (NTS, ZTS) | GCC | `cubrid.so` (CCI and OpenSSL linked statically) |
| Windows x64 | 8.4, 8.5 (NTS, TS) | Visual Studio 2022 (vs17) | `php_cubrid.dll` + `cascci.dll` |

32-bit platforms and ARM64 are not supported. PHP 8.2 and earlier are not supported.

End users usually do not need to build the driver. See the CUBRID manual for installing it with PIE or from the release packages: <https://www.cubrid.org/manual/en/11.4/api/php.html>.

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

## 4. Testing

The tests need a running CUBRID server (a broker on port `33000` with `demodb`). Build the driver with `./build.sh <ver>` first; the test runners load `build_linux/<ver>/modules/cubrid.so` without reading `php.ini`.

| Directory | Runner | Description |
|---|---|---|
| `tests_8x` | `run_tests.sh` / `run_tests.bat` | Function tests (`.phpt`, run-tests.php). See `tests_8x/README.md`. |
| `tests2_8x` | `choseRunCases.sh` / `choseRunCases.bat` | Scenario tests including large data and LOB cases. |

```
cd tests_8x
PHP_BIN=~/php/8.4/bin/php CUBRID_TEST_HOST=localhost ./run_tests.sh

cd tests2_8x
./choseRunCases.sh ~/php/8.4/bin/php -S
```

## 5. Making a release

A release is published as a GitHub release of the tag `<version>` (without a `v` prefix), where `<version>` is `PHP_CUBRID_VERSION` in `php_cubrid_version.h`. The release assets follow the [PIE](https://github.com/php/pie) naming conventions, so `pie install cubrid/cubrid` finds them; the same files are used for manual installation.

| Asset | Made by | Used for |
|---|---|---|
| `php_cubrid-<version>-src.tgz` | `script/release.sh` | Linux: PIE (pre-packaged source), manual build |
| `php_cubrid-<version>-<8.4\|8.5>-<nts\|ts>-vs17-x86_64.zip` | `script/release.ps1` | Windows: PIE, manual installation |
| `*.sha256` | both scripts | SHA-256 checksum of each file (`sha256sum -c` format) |

### 5.1 Linux source archive: script/release.sh

```
script/release.sh [--ref REV] [--output DIR] [--check [PHP_CONFIG]]

script/release.sh                                   # HEAD
script/release.sh --ref <version> --check ~/php/8.4/bin/php-config
```

- The archive is made from a committed revision (HEAD by default) and the `cci-src` commit recorded in it; uncommitted changes and build outputs are never packaged.
- The top directory in the archive is `php_cubrid-<version>-src/`.
- In the packaged `cci-src` (the cci-src repository itself is not changed): `build.sh` does not stop when git is missing, `BUILD_NUMBER` holds the full CCI version (e.g. `11.2.0.0040-9965aa8`), and `win/` is left out.
- `--check` extracts the archive into `script/release/check` and builds it with `phpize; ./configure; make` as PIE does, then checks that the module loads.
- Output: `script/release/` (ignored by git). The same directory is used by `script/release.ps1`.

### 5.2 Windows binaries: script/release.ps1

```
.\script\release.ps1 [-Targets 84,85] [-Flavors nts,ts] [-PhpSdk DIR] [-OutputDir DIR] [-Clean]

.\script\release.ps1                                # 8.4/8.5 x NTS/TS = 4 zips
```

Each target is built with `build_win.ps1`. Each zip contains:

- `php_cubrid-<version>-<x.y>-<nts|ts>-vs17-x86_64.dll`: the extension, named like the zip as PIE requires (PIE installs it as `ext\php_cubrid.dll`; rename it when installing by hand)
- `.pdb` of the extension, when built
- `cascci.dll` (PIE copies it next to `php.exe`)
- `LICENSE`

### 5.3 Publishing

Once the package is on Packagist (5.4), a tag pushed to the official repository is offered to users at once. Prepare the release files first, then push the tag and create the GitHub release right after it, so that the time without release files is short.

1. Update `PHP_CUBRID_VERSION` in `php_cubrid_version.h` and the release notes, commit, and merge the change into `develop` of <https://github.com/CUBRID/cubrid-php>.
2. Tag the commit locally (do not push it yet): `git tag <version>`.
3. Make the release files from the tag: `script/release.sh --ref <version> --check` on Linux, and `script\release.ps1` on Windows on a checkout of the same commit. Copy all files into one `script/release` directory.
4. Push the tag and create the GitHub release right away:

   ```
   git push https://github.com/CUBRID/cubrid-php.git <version>
   gh release create <version> --repo CUBRID/cubrid-php --title "<version>" --notes-file <release-notes> \
       script/release/php_cubrid-<version>-*
   ```
5. Check that <https://packagist.org/packages/cubrid/cubrid> shows the new version, and install it with `pie install cubrid/cubrid:<version>`.

To test a release before publishing it, see [6. Testing a release with PIE](#6-testing-a-release-with-pie).

> [!WARNING]
> The source archive asset must be attached to the GitHub release. Without it, PIE falls back to the GitHub source zip, which does not contain the `cci-src` submodule, and the build fails.

### 5.4 Registering on Packagist (once)

PIE finds packages on [Packagist](https://packagist.org). Until `cubrid/cubrid` is registered there, users must add the repository with `pie repository:add vcs https://github.com/CUBRID/cubrid-php` first.

1. Make sure `composer.json` is on the default branch (`develop`) of <https://github.com/CUBRID/cubrid-php>. Packagist reads the package name (`cubrid/cubrid`) from it.
2. Sign in to <https://packagist.org> with **Login with GitHub**, preferably with a GitHub account that has admin access to the repository, so that the webhook is set up automatically.
3. Click **Submit**, enter `https://github.com/CUBRID/cubrid-php`, click **Check** and then **Submit**.
4. On the package page, add the other maintainers under **Maintainers**. The account that registers the first `cubrid/*` package owns the `cubrid` vendor name; keep at least two maintainers.
5. Check the automatic update. If the package page shows that it is not auto-updated, add a webhook in the GitHub repository (Settings > Webhooks > Add webhook):

   | Field | Value |
   |---|---|
   | Payload URL | `https://packagist.org/api/github?username=<packagist user name>` |
   | Content type | `application/json` |
   | Secret | the API token on the Packagist profile page |
   | Events | Just the push event |
6. Check that the package page lists the released versions (older tags without `composer.json` are not listed), and that `pie install cubrid/cubrid` works without `pie repository:add`.
7. Remove the `pie repository:add` step from the user documents: `build-guide.md` (4.2), `README.md` and the CUBRID manual (`api/php.rst`, `api/pdo.rst`, ko and en).

After registration, an install makes only about two GitHub API requests (the release lookup and the asset download), so the GitHub API rate limit is rarely reached.

## 6. Testing a release with PIE

Releases are made only on the official repository <https://github.com/CUBRID/cubrid-php>. A release can be tested with PIE as a GitHub **pre-release** of that repository before it is announced. Until the package is registered on Packagist, PIE reads it from the repository directly (`pie repository:add vcs`).

> [!WARNING]
> A tag pushed to the official repository is public, and once the package is on Packagist it is offered to users as soon as it is pushed. Push a tag only for a version that is ready to be released. To test before tagging, use the offline test of the source archive in 6.4.

### 6.1 How PIE finds the release files

- The package version is the **git tag** (`<version>`, without `v`). The tagged commit must contain `composer.json`.
- PIE looks up the release with the GitHub API `GET /repos/CUBRID/cubrid-php/releases/tags/<version>`, which returns **published releases only**. A **pre-release** works; a **draft does not** (a draft is not returned by the API, and its tag is not created until it is published).
- The asset names must match exactly (compared in lower case):
  - Linux: `php_cubrid-<version>-src.tgz`
  - Windows: `php_cubrid-<version>-<x.y>-<nts|ts>-vs17-x86_64.zip`, where PIE takes `<x.y>`, NTS/TS, the compiler (`vs17`) and the architecture from the target PHP (`php -i`: "PHP Extension Build", e.g. `API20240924,TS,VS17` for PHP 8.4 TS)

### 6.2 Preparing a pre-release

```
# 1. tag the commit to test (the version is PHP_CUBRID_VERSION in php_cubrid_version.h)
git tag <version>
git push https://github.com/CUBRID/cubrid-php.git <version>

# 2. make the packages from the tag
script/release.sh --ref <version> --check          # Linux
.\script\release.ps1                                # Windows, on a checkout of the same tag

# 3. create a pre-release (not a draft) and upload all files of script/release
gh release create <version> --repo CUBRID/cubrid-php --prerelease \
    --title "<version>" --notes-file <release-notes> \
    script/release/php_cubrid-<version>-*
```

The asset names contain the version, so a different version needs a different `PHP_CUBRID_VERSION` as well. After the test, make the release a full release (`gh release edit <version> --repo CUBRID/cubrid-php --prerelease=false`).

### 6.3 GitHub token

Without authentication the GitHub API allows 60 requests per hour per IP address. A `vcs` repository (`pie repository:add vcs`) uses many requests per install, so PIE (Composer) soon stops with a `Token (hidden):` prompt.

- Create a personal access token in the **personal** account settings (profile picture > Settings > Developer settings > Personal access tokens, <https://github.com/settings/personal-access-tokens>); organization and repository settings do not have it. A fine-grained token with "Public repositories (read-only)", or a classic token without scopes, is enough.
- Enter it at the `Token (hidden):` prompt (stored in the global Composer `auth.json`), or pass it for the session only:

  ```
  export COMPOSER_AUTH='{"github-oauth":{"github.com":"<token>"}}'          # Linux
  $env:COMPOSER_AUTH = '{"github-oauth":{"github.com":"<token>"}}'         # Windows PowerShell
  ```
- Revoke the token after the test, and remove a stored one with `composer config --global --unset github-oauth.github.com` (or delete it from `auth.json`).

After the release is on Packagist, an install makes only about two GitHub API requests (the release lookup and the asset download), so users need a token only for many installs from one IP address (CI, shared NAT).

### 6.4 Testing on Linux

```
# PIE needs PHP 8.1 or later with the openssl and zip extensions
curl -fL -o pie.phar https://github.com/php/pie/releases/latest/download/pie.phar
sudo install -m 755 pie.phar /usr/local/bin/pie

pie repository:add vcs https://github.com/CUBRID/cubrid-php         # not needed once the package is on Packagist
pie install cubrid/cubrid:<version>                         # add --with-php-config=PATH for another PHP
php --ri cubrid
```

- Run `pie` **without** `sudo`; PIE asks for the password itself when it installs the module. With `sudo pie`, the token stored for your user is not used.
- Expected output: `Found prebuilt archive: .../php_cubrid-<version>-src.tgz`, the build log, and the extension enabled in an ini file (or a warning that it could not be enabled).
- `php --ri cubrid` shows `Driver Version => <version>`.

Offline test of an extracted source archive (no GitHub access; the package version is `*@dev`):

```
tar xzf php_cubrid-<version>-src.tgz
pie repository:add path $PWD/php_cubrid-<version>-src
pie install cubrid/cubrid:*@dev
```

### 6.5 Testing on Windows

Use the PIE executable, which contains its own PHP and needs no `php.ini` changes. Run PowerShell as administrator when PHP is in a protected directory such as `C:\Program Files`, because PIE writes into the PHP directory.

```
Invoke-WebRequest -Uri https://github.com/php/pie/releases/latest/download/pie-Windows-X64.exe -OutFile pie.exe

.\pie.exe repository:add vcs https://github.com/CUBRID/cubrid-php   # not needed once the package is on Packagist
.\pie.exe install --with-php-path=D:\PHP\php-8.5.8-Win32-vs17-x64\php.exe cubrid/cubrid:<version>
D:\PHP\php-8.5.8-Win32-vs17-x64\php.exe --ri cubrid
```

To use `pie.phar` instead (`php pie.phar ...`), the PHP that runs it needs `openssl` and `zip`. The windows.php.net packages have no `php.ini`, so create one from `php.ini-development` and set:

```
extension_dir = "D:\PHP\php-8.5.8-Win32-vs17-x64\ext"     ; absolute: a relative "ext" is resolved from the current directory
extension=openssl
extension=zip
```

Check the result:

- Expected output: `Found prebuilt archive: .../php_cubrid-<version>-<x.y>-<nts|ts>-vs17-x86_64.zip`. A package directory name without `nts` (e.g. `php-8.5.8-Win32-vs17-x64`) is a TS build and uses the `ts` zip.
- Installed files: `ext\php_cubrid.dll`, `cascci.dll` next to `php.exe`, `extras\cubrid\LICENSE`, and `extension=cubrid` in `php.ini`.
- `php --ri cubrid` shows the driver version. If the module does not load, check `cascci.dll` next to `php.exe` and the Microsoft Visual C++ 2015-2022 Redistributable (x64).

### 6.6 Cleaning up

```
pie uninstall cubrid/cubrid                                   # disable and remove the extension
pie repository:remove https://github.com/CUBRID/cubrid-php
# only when the pre-release is withdrawn (a failed release):
gh release delete <version> --repo CUBRID/cubrid-php --cleanup-tag
git tag -d <version>
```

Revoke the test token as described in 6.3. On Windows, `cascci.dll` next to `php.exe` may have to be removed by hand when no other CUBRID driver uses it.

### 6.7 Troubleshooting

| Message | Cause and solution |
|---|---|
| `Token (hidden):` | GitHub API rate limit. Enter a token (6.3). |
| `Could not find release asset for cubrid/cubrid named one of "..."` / `Windows archive with prebuilt extension ... was not attached on release ...` | The release is a draft, the tag differs from the version in the asset names, or the file for this PHP (version, NTS/TS) is not uploaded. The message lists the expected names. |
| `cas_cci.h not found` during configure | The `-src.tgz` asset was not found, so PIE fell back to the GitHub source zip, which has no `cci-src`. Upload `php_cubrid-<version>-src.tgz`. |
| `Could not open input file: pie.phar` | `pie.phar` is not in the current directory; download it or give its path. |
| `PIE requires the "openssl" extension, but it is not loaded` | Enable `openssl` (and `zip`) for the PHP that runs `pie.phar`, or use `pie-Windows-X64.exe`. |
| `Unable to load dynamic library 'openssl' (tried: C:\php\ext\...)` | `extension_dir` is not set, so the built-in default `C:\php\ext` is used. Set an absolute `extension_dir`. |
