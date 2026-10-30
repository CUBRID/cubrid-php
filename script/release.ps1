<#
.SYNOPSIS
    Make the Windows binary release of the CUBRID PHP driver (php_cubrid.dll).

.DESCRIPTION
    .\script\release.ps1 [options]
        -> script\release\php_cubrid-<version>-<php x.y>-<nts|ts>-vs17-x86_64.zip
           script\release\php_cubrid-<version>-<php x.y>-<nts|ts>-vs17-x86_64.zip.sha256

    Each target (PHP version x TS/NTS) is built with build_win.ps1 and packaged
    into one zip. <version> is PHP_CUBRID_VERSION of php_cubrid_version.h.

    The zip follows the PIE Windows binary convention, so it is used as is by
    PIE (GitHub release asset of tag <version>) and by users who install it by hand:
        php_cubrid-<...>.dll   the extension (PIE installs it as ext\php_cubrid.dll)
        php_cubrid-<...>.pdb   debug symbols, when built
        cascci.dll             the CCI library; it must be next to php.exe or in PATH
                               (PIE copies it next to php.exe)
        LICENSE                (PIE copies it to extras\cubrid)

    Requirements: the same as build_win.ps1 (Visual Studio 2022, Git for Windows).
    The PHP binaries, devel packs and PHP SDK are downloaded by build_win.ps1.

.PARAMETER Targets
    PHP versions to build: 84, 85 (default: all).

.PARAMETER Flavors
    nts, ts (default: both).

.PARAMETER PhpSdk
    Existing php-sdk-binary-tools directory, passed to build_win.ps1.

.PARAMETER OutputDir
    Output directory (default: script\release).

.PARAMETER Clean
    Build everything from scratch (build_win.ps1 -Clean on the first target).

.EXAMPLE
    .\script\release.ps1
    .\script\release.ps1 -Targets 85 -Flavors nts
    .\script\release.ps1 -PhpSdk D:\php-sdk -Clean
#>

param(
    [ValidateSet('84', '85')]
    [string[]]$Targets = @('84', '85'),
    [ValidateSet('nts', 'ts')]
    [string[]]$Flavors = @('nts', 'ts'),
    [string]$PhpSdk,
    [string]$OutputDir,
    [switch]$Clean
)

$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'

# ---- per-driver settings ----------------------------------------------------
$ExtName = 'cubrid'
$VersionHeader = 'php_cubrid_version.h'
$VersionMacro = 'PHP_CUBRID_VERSION'
# -----------------------------------------------------------------------------
$Compiler = 'vs17'
$Arch = 'x86_64'

$Root = Split-Path $PSScriptRoot -Parent
$BuildScript = Join-Path $Root 'build_win.ps1'
if (-not $OutputDir) { $OutputDir = Join-Path $PSScriptRoot 'release' }
New-Item -ItemType Directory -Force -Path $OutputDir | Out-Null
$OutputDir = (Resolve-Path $OutputDir).Path
$StageRoot = Join-Path $PSScriptRoot 'release\stage'

# ---- version ----------------------------------------------------------------
$header = Get-Content (Join-Path $Root $VersionHeader) -Raw
if ($header -notmatch "#define\s+$VersionMacro\s+`"(\d+\.\d+\.\d+\.\d+)`"") {
    throw "cannot read $VersionMacro from $VersionHeader"
}
$Version = $Matches[1]
Write-Host "[INFO] driver  : $ExtName $Version"

if (Get-Command git -ErrorAction SilentlyContinue) {
    $dirty = & git -C $Root status --porcelain --untracked-files=no
    if ($dirty) { Write-Warning "the work tree has uncommitted changes; they are built into the release" }
    & git -C $Root rev-parse --verify -q "refs/tags/$Version" *> $null
    if ($LASTEXITCODE -ne 0) {
        Write-Host "[INFO] tag $Version does not exist yet (PIE needs a GitHub release of tag $Version)"
    }
}

function Write-Sha256 {
    param([string]$File)
    $hash = (Get-FileHash -Algorithm SHA256 -Path $File).Hash.ToLowerInvariant()
    # sha256sum format ("<hash>  <file>", LF, no BOM) so that "sha256sum -c" works too.
    [IO.File]::WriteAllText("$File.sha256", "$hash  $(Split-Path $File -Leaf)`n")
    return $hash
}

$results = @()
$first = $true
foreach ($target in $Targets) {
    foreach ($flavor in $Flavors) {
        $phpXY = $target.Substring(0, 1) + '.' + $target.Substring(1)
        Write-Host ''
        Write-Host "==== PHP $phpXY $flavor ===="

        $buildArgs = @{ Target = $target }
        if ($flavor -eq 'ts') { $buildArgs.ThreadSafe = $true }
        if ($PhpSdk) { $buildArgs.PhpSdk = $PhpSdk }
        if ($Clean -and $first) { $buildArgs.Clean = $true }
        $first = $false
        & $BuildScript @buildArgs

        $buildDir = Join-Path $Root "build_win\$target-$flavor"
        $modules = Join-Path $buildDir 'modules'
        $dll = Join-Path $modules "php_$ExtName.dll"
        $cci = Join-Path $modules 'cascci.dll'
        foreach ($f in @($dll, $cci)) {
            if (-not (Test-Path $f)) { throw "$f not found after build_win.ps1 $target ($flavor)" }
        }
        $pdb = Get-ChildItem -Path $buildDir -Recurse -Filter "php_$ExtName.pdb" -ErrorAction SilentlyContinue |
            Where-Object { $_.DirectoryName -ne $modules } |
            Sort-Object LastWriteTime -Descending | Select-Object -First 1

        $name = "php_$ExtName-$Version-$phpXY-$flavor-$Compiler-$Arch"
        $stage = Join-Path $StageRoot $name
        if (Test-Path $stage) { Remove-Item -Recurse -Force $stage }
        New-Item -ItemType Directory -Force -Path $stage | Out-Null

        # PIE looks for the DLL named like the zip itself.
        Copy-Item $dll (Join-Path $stage "$name.dll")
        if ($pdb) { Copy-Item $pdb.FullName (Join-Path $stage "$name.pdb") }
        Copy-Item $cci $stage
        Copy-Item (Join-Path $Root 'LICENSE') $stage

        $zip = Join-Path $OutputDir "$name.zip"
        if (Test-Path $zip) { Remove-Item -Force $zip }
        Compress-Archive -Path (Join-Path $stage '*') -DestinationPath $zip
        $hash = Write-Sha256 $zip
        $results += [pscustomobject]@{ Zip = $zip; Sha256 = $hash }
    }
}

if (Test-Path $StageRoot) { Remove-Item -Recurse -Force $StageRoot }

Write-Host ''
Write-Host '==== release ===='
foreach ($r in $results) {
    Write-Host "$($r.Zip)"
    Write-Host "    sha256: $($r.Sha256)"
}
