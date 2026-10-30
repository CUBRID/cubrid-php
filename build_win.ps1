<#
.SYNOPSIS
    Build the CUBRID PHP extension (php_cubrid.dll) on Windows with Visual Studio 2022 (vs17).

.DESCRIPTION
    .\build_win.ps1 <ver> [options]   ->  build_win\<ver>-<nts|ts>\modules\php_cubrid.dll
        ver: 84, 85 (php_cubrid8.c)

    1. CCI (cci-src) is built once with CMake as cascci.dll and shared by all targets
       (build_win\cci\{bin,lib,include}).
    2. The PHP devel pack (phpize), the PHP binary of the same version and the
       PHP SDK binary tools (bison, sed, re2c required by configure) are downloaded
       into build_win\downloads.
    3. The sources are copied into build_win\<ver>-<nts|ts> and built with
       phpize / configure --with-cubrid / nmake, so the source tree stays clean.

    Requirements: Visual Studio 2022 with "Desktop development with C++" (MSVC v143,
    CMake), Git for Windows (the CCI CMake uses git and awk of Git\usr\bin).

.PARAMETER PhpVersion
    Full PHP version as in the file names of windows.php.net, such as 8.4.14 or
    8.4.27-1 (default: latest release of <ver>).

.PARAMETER DevelPack
    Existing PHP devel pack directory (containing phpize.bat); skips the download.

.PARAMETER PhpDir
    Existing PHP directory (containing php.exe), such as
    D:\PHP\php-8.5.8-Win32-vs17-x64; skips the PHP download.
    The PHP version and TS/NTS are taken from its php.exe (-ThreadSafe is not
    needed), and the devel pack is searched for in this order:
      - phpize.bat under <PhpDir> (e.g. the devel pack extracted to <PhpDir>\devel)
      - a sibling directory php-devel-pack-<ver>-[nts-]Win32-vs17-x64 or
        php-<ver>-[nts-]devel-vs17-x64 next to <PhpDir>
    and is downloaded only for that exact version if none is found.

.PARAMETER PhpSdk
    Existing php-sdk-binary-tools directory; skips the download.

.PARAMETER ThreadSafe
    Build for thread safe (TS) PHP. Default is NTS.

.PARAMETER Clean
    Remove the build directory (and the CCI build) and build from scratch.

.EXAMPLE
    .\build_win.ps1 84
    .\build_win.ps1 85 -ThreadSafe
    .\build_win.ps1 84 -PhpVersion 8.4.14 -Clean
    .\build_win.ps1 85 -PhpDir D:\PHP\php-8.5.8-Win32-vs17-x64
    .\build_win.ps1 85 -PhpDir D:\PHP\php-8.5.8-nts-Win32-vs17-x64 -DevelPack D:\PHP\php-8.5.8-devel-vs17-x64 -PhpSdk D:\php-sdk
#>

param(
    [Parameter(Mandatory = $true, Position = 0)]
    [ValidateSet('84', '85')]
    [string]$Target,
    [string]$PhpVersion,
    [string]$DevelPack,
    [string]$PhpDir,
    [string]$PhpSdk,
    [switch]$ThreadSafe,
    [switch]$Clean
)

$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'   # Invoke-WebRequest is very slow with the progress bar
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

$Root = $PSScriptRoot
$PhpXY = $Target.Substring(0, 1) + '.' + $Target.Substring(1)

# An existing PHP directory decides the version and TS/NTS.
if ($PhpDir) {
    $PhpDir = (Resolve-Path $PhpDir).Path
    $exe = Join-Path $PhpDir 'php.exe'
    if (-not (Test-Path $exe)) { throw "php.exe not found in $PhpDir" }
    # No double quotes in -r: Windows PowerShell 5.1 strips them from native arguments.
    $info = & $exe -n -r 'echo PHP_VERSION, PHP_EOL, PHP_ZTS ? 1 : 0;'
    if ($LASTEXITCODE -ne 0 -or $info.Count -ne 2) { throw "Cannot run $exe" }
    $exeVersion = $info[0]
    $exeTs = $info[1] -eq '1'
    $exeFlavor = if ($exeTs) { 'ts' } else { 'nts' }
    if ($PSBoundParameters.ContainsKey('ThreadSafe') -and [bool]$ThreadSafe -ne $exeTs) {
        throw "-ThreadSafe:$([bool]$ThreadSafe) does not match $exe ($exeFlavor)"
    }
    $ThreadSafe = [switch]$exeTs
    if (-not $PhpVersion) {
        # Keep the respin suffix of the directory name (php-8.4.27-1-Win32-vs17-x64).
        $leaf = Split-Path $PhpDir -Leaf
        if ($leaf -match "^php-($([regex]::Escape($exeVersion))(-\d+)?)-") { $PhpVersion = $Matches[1] }
        else { $PhpVersion = $exeVersion }
    }
    if (-not $PhpVersion.StartsWith($exeVersion)) { throw "-PhpVersion $PhpVersion does not match $exe ($exeVersion)" }
    Write-Host "PHP: $PhpDir ($PhpVersion, $exeFlavor)"
}

$Flavor = if ($ThreadSafe) { 'ts' } else { 'nts' }
$WinRoot = Join-Path $Root 'build_win'
$Downloads = Join-Path $WinRoot 'downloads'
$CciBuild = Join-Path $WinRoot 'cci-build'
$CciOut = Join-Path $WinRoot 'cci'
$BuildDir = Join-Path $WinRoot "$Target-$Flavor"
$PhpSdkTag = 'php-sdk-2.9.0'

function Invoke-Checked {
    param([string]$Exe, [string[]]$Arguments)
    Write-Host "> $Exe $($Arguments -join ' ')"
    & $Exe @Arguments
    if ($LASTEXITCODE -ne 0) { throw "$Exe failed (exit code $LASTEXITCODE)" }
}

# Load the x64 build environment of Visual Studio 2022 into this process.
function Import-VsEnv {
    $vswhere = Join-Path ${env:ProgramFiles(x86)} 'Microsoft Visual Studio\Installer\vswhere.exe'
    if (-not (Test-Path $vswhere)) { throw "vswhere.exe not found. Install Visual Studio 2022." }
    $vs = & $vswhere -version '[17.0,18.0)' -products * `
        -requires Microsoft.VisualStudio.Component.VC.Tools.x86.x64 -property installationPath |
        Select-Object -First 1
    if (-not $vs) { throw "Visual Studio 2022 (vs17) with the C++ x64 tools not found." }
    $vcvars = Join-Path $vs 'VC\Auxiliary\Build\vcvars64.bat'
    Write-Host "Visual Studio: $vs"
    if ($env:VSCMD_VER -and $env:VSCMD_ARG_TGT_ARCH -eq 'x64' -and (Get-Command cl.exe -ErrorAction SilentlyContinue)) {
        # Already loaded (e.g. a previous run in this session): running vcvars64.bat
        # again keeps growing PATH until cmd fails with "The input line is too long".
        Write-Host "Visual Studio environment already loaded ($env:VSCMD_VER)"
    } else {
        $vars = cmd /c "`"$vcvars`" >nul && set"
        if ($LASTEXITCODE -ne 0) { throw "$vcvars failed (exit code $LASTEXITCODE). PATH may be too long; open a new shell." }
        $vars | ForEach-Object {
            if ($_ -match '^([^=]+)=(.*)$') { Set-Item -Path "env:$($Matches[1])" -Value $Matches[2] }
        }
    }
    if (-not (Get-Command cl.exe -ErrorAction SilentlyContinue)) { throw "cl.exe not found after vcvars64.bat" }
    if (-not (Get-Command cmake.exe -ErrorAction SilentlyContinue)) {
        throw "cmake.exe not found. Install the 'C++ CMake tools for Windows' component or CMake."
    }

    # The CCI CMake runs git and awk. Append (not prepend) Git\usr\bin so that
    # its link.exe does not hide the MSVC linker.
    $git = Get-Command git.exe -ErrorAction SilentlyContinue
    if (-not $git) { throw "git.exe not found. Install Git for Windows." }
    $gitUsrBin = Join-Path (Split-Path (Split-Path $git.Source)) 'usr\bin'
    Add-PathSuffix $gitUsrBin
}

# Append directories to PATH once (the script may run several times in one session).
function Add-PathSuffix {
    param([string[]]$Dirs)
    $cur = $env:PATH -split ';'
    foreach ($d in $Dirs) {
        if ((Test-Path $d) -and ($cur -notcontains $d)) { $env:PATH = "$env:PATH;$d"; $cur += $d }
    }
}

function Get-Package {
    param([string]$FileName, [string[]]$Urls)
    if (-not $FileName) { throw "Get-Package: no file name" }
    $zip = Join-Path $Downloads $FileName
    $dir = $zip -replace '\.zip$', ''
    # An empty directory is left over from an interrupted extraction.
    if ((Test-Path $dir) -and -not (Get-ChildItem -Path $dir -Force | Select-Object -First 1)) {
        Write-Host "Removing empty $dir"
        Remove-Item -Recurse -Force $dir
    }
    if (Test-Path $dir) { return $dir }

    New-Item -ItemType Directory -Force -Path $Downloads | Out-Null
    # Download and extract into temporary names so that an interruption leaves no
    # half-written zip or directory that a later run would take as complete.
    $tmpZip = "$zip.partial"
    $tmpDir = "$dir.partial"
    foreach ($try in 1, 2) {
        if (-not (Test-Path $zip)) {
            $ok = $false
            foreach ($url in $Urls) {
                try {
                    Write-Host "Downloading $url"
                    Invoke-WebRequest -Uri $url -OutFile $tmpZip -UseBasicParsing
                    Move-Item -Force $tmpZip $zip
                    $ok = $true
                    break
                } catch {
                    Remove-Item -Force -ErrorAction SilentlyContinue $tmpZip
                }
            }
            if (-not $ok) { throw "Cannot download $FileName" }
        }
        Remove-Item -Recurse -Force -ErrorAction SilentlyContinue $tmpDir
        try {
            Expand-Archive -Path $zip -DestinationPath $tmpDir
            break
        } catch {
            Remove-Item -Recurse -Force -ErrorAction SilentlyContinue $tmpDir
            if ($try -eq 2) { throw "Cannot extract ${zip}: $_" }
            Write-Host "Cannot extract $zip ($_), downloading again"
            Remove-Item -Force $zip
        }
    }
    Move-Item $tmpDir $dir
    return $dir
}

function Get-PhpPackage {
    param([string]$FileName)
    return Get-Package $FileName @("https://windows.php.net/downloads/releases/$FileName",
                                   "https://windows.php.net/downloads/releases/archives/$FileName")
}

function Build-Cci {
    if ((Test-Path (Join-Path $CciOut 'lib\cascci.lib')) -and (Test-Path (Join-Path $CciOut 'bin\cascci.dll'))) {
        Write-Host "CCI: $CciOut (already built)"
        return
    }
    # CMAKE_POLICY_VERSION_MINIMUM: cci-src requires CMake 2.8, which CMake 4.x rejects.
    Invoke-Checked cmake @('-S', (Join-Path $Root 'cci-src'), '-B', $CciBuild,
        '-G', 'Visual Studio 17 2022', '-A', 'x64', '-DCMAKE_POLICY_VERSION_MINIMUM=3.5')
    Invoke-Checked cmake @('--build', $CciBuild, '--config', 'Release', '--target', 'cascci')

    $dll = Get-ChildItem -Path $CciBuild -Recurse -Filter cascci.dll |
        Where-Object { $_.DirectoryName -like '*\Release' } | Select-Object -First 1
    $lib = Get-ChildItem -Path $CciBuild -Recurse -Filter cascci.lib |
        Where-Object { $_.DirectoryName -like '*\Release' } | Select-Object -First 1
    if (-not $dll -or -not $lib) { throw "cascci.dll / cascci.lib not found under $CciBuild" }

    foreach ($d in 'bin', 'lib', 'include') {
        New-Item -ItemType Directory -Force -Path (Join-Path $CciOut $d) | Out-Null
    }
    Copy-Item $dll.FullName (Join-Path $CciOut 'bin')
    Copy-Item $lib.FullName (Join-Path $CciOut 'lib')
    foreach ($h in 'cas_cci.h', 'broker_cas_error.h', 'compat_dbtran_def.h') {
        Copy-Item (Join-Path $Root "cci-src\src\cci\$h") (Join-Path $CciOut 'include')
    }
    Write-Host "CCI: $CciOut"
}

if ($Clean) {
    Remove-Item -Recurse -Force -ErrorAction SilentlyContinue $BuildDir, $CciBuild, $CciOut
}

Import-VsEnv

# PHP devel pack and binary
$pkgFlavor = if ($ThreadSafe) { '' } else { 'nts-' }
$develZip = $null
$phpZip = $null
if ($PhpDir -and -not $DevelPack) {
    # A devel pack extracted inside or next to the PHP directory.
    $candidates = @($PhpDir) + @(Get-ChildItem -Path (Split-Path $PhpDir) -Directory |
        Where-Object { $_.Name -like "php-devel-pack-$PhpVersion-*" -or $_.Name -like "php-$PhpVersion-*devel*" } |
        ForEach-Object { $_.FullName })
    foreach ($c in $candidates) {
        foreach ($p in @(Get-ChildItem -Path $c -Recurse -Depth 2 -Filter phpize.bat -ErrorAction SilentlyContinue)) {
            # The devel pack must match the TS/NTS of php.exe.
            $cfg = Join-Path $p.DirectoryName 'include\main\config.w32.h'
            if (-not (Test-Path $cfg)) { continue }
            $zts = [bool](Select-String -Path $cfg -Pattern '^\s*#\s*define\s+ZTS\s+1' -Quiet)
            if ($zts -ne [bool]$ThreadSafe) { continue }
            $DevelPack = $p.DirectoryName
            break
        }
        if ($DevelPack) { Write-Host "Devel pack: $DevelPack"; break }
    }
}
if (-not $DevelPack -or -not $PhpDir) {
    if ($PhpVersion) {
        $develZip = "php-devel-pack-$PhpVersion-${pkgFlavor}Win32-vs17-x64.zip"
        $phpZip = "php-$PhpVersion-${pkgFlavor}Win32-vs17-x64.zip"
    } else {
        # The version may carry a respin suffix (e.g. 8.4.27-1), so take the file names as listed.
        $releases = Invoke-RestMethod -Uri 'https://windows.php.net/downloads/releases/releases.json' -UseBasicParsing
        $build = $releases.$PhpXY."$Flavor-vs17-x64"
        if (-not $build) { throw "No PHP $PhpXY $Flavor-vs17-x64 release found in releases.json (use -PhpVersion)" }
        $PhpVersion = $releases.$PhpXY.version
        $develZip = $build.devel_pack.path
        $phpZip = $build.zip.path
    }
}
if ($PhpVersion -and -not $PhpVersion.StartsWith("$PhpXY.")) { throw "-PhpVersion $PhpVersion is not PHP $PhpXY" }
if (-not $DevelPack) {
    $pkg = Get-PhpPackage $develZip
    # The zip has one top directory (php-<ver>-devel-vs17-x64).
    $DevelPack = (Get-ChildItem -Path $pkg -Recurse -Filter phpize.bat | Select-Object -First 1).DirectoryName
    if (-not $DevelPack) { throw "phpize.bat not found in $pkg (remove it and run again)" }
}
if (-not $PhpDir) {
    $PhpDir = Get-PhpPackage $phpZip
}
if (-not $PhpSdk) {
    $pkg = Get-Package "$PhpSdkTag.zip" @("https://github.com/php/php-sdk-binary-tools/archive/refs/tags/$PhpSdkTag.zip")
    # The zip has one top directory (php-sdk-binary-tools-<tag>).
    $PhpSdk = (Get-ChildItem -Path $pkg -Recurse -Filter phpsdk_version.bat | Select-Object -First 1).Directory.Parent.FullName
    if (-not $PhpSdk) { throw "phpsdk_version.bat not found in $pkg (remove it and run again)" }
}
$sdkTools = (Join-Path $PhpSdk 'bin'), (Join-Path $PhpSdk 'msys2\usr\bin')
foreach ($d in $sdkTools) { if (-not (Test-Path $d)) { throw "$d not found (PHP SDK: $PhpSdk)" } }
# Appended so that the MSVC tools keep precedence.
Add-PathSuffix $sdkTools

$phpize = Join-Path $DevelPack 'phpize.bat'
$phpExe = Join-Path $PhpDir 'php.exe'
if (-not (Test-Path $phpize)) { throw "phpize.bat not found in $DevelPack" }
if (-not (Test-Path $phpExe)) { throw "php.exe not found in $PhpDir" }

Build-Cci

# Build the extension out of the source tree.
New-Item -ItemType Directory -Force -Path $BuildDir | Out-Null
Copy-Item (Join-Path $Root 'config.w32') $BuildDir
Copy-Item (Join-Path $Root 'php_cubrid*.c'), (Join-Path $Root 'php_cubrid*.h') $BuildDir
Push-Location $BuildDir
try {
    Invoke-Checked $phpize @()
    Invoke-Checked (Join-Path $BuildDir 'configure.bat') @("--with-cubrid=$CciOut")
    Invoke-Checked nmake.exe @('/nologo')
} finally {
    Pop-Location
}

$dll = Get-ChildItem -Path $BuildDir -Recurse -Filter php_cubrid.dll |
    Where-Object { $_.DirectoryName -notlike '*\modules' } |
    Sort-Object LastWriteTime -Descending | Select-Object -First 1
if (-not $dll) { throw "php_cubrid.dll not found under $BuildDir" }

# php_cubrid.dll needs cascci.dll next to it (or in PATH).
$Modules = Join-Path $BuildDir 'modules'
New-Item -ItemType Directory -Force -Path $Modules | Out-Null
Copy-Item $dll.FullName $Modules
Copy-Item (Join-Path $CciOut 'bin\cascci.dll') $Modules
$ext = Join-Path $Modules 'php_cubrid.dll'

# Load check
if (($env:PATH -split ';') -notcontains $Modules) { $env:PATH = "$Modules;$env:PATH" }
# The code goes through stdin: Windows PowerShell 5.1 strips the double quotes of -r '...'.
$loaded = '<?php echo phpversion("cubrid");' | & $phpExe -n -d "extension=$ext"
if ($LASTEXITCODE -ne 0 -or -not $loaded) { throw "php_cubrid.dll does not load in $phpExe" }

Write-Host ''
Write-Host "PHP     : $phpExe ($(& $phpExe -n -r 'echo PHP_VERSION;'), $Flavor)"
Write-Host "Module  : $ext (cubrid $loaded)"
Write-Host "Test    : `$env:PATH = `"$Modules;`$env:PATH`""
Write-Host "          & `"$phpExe`" run-tests.php -q -n -d extension=$ext -p `"$phpExe`" <tests>"
