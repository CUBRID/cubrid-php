#!/bin/bash
#
# Make the Linux source release of the CUBRID PHP driver.
#
#   script/release.sh [options]
#     ->  script/release/php_cubrid-<version>-src.tgz
#         script/release/php_cubrid-<version>-src.tgz.sha256
#
# The archive is made from a committed revision (HEAD by default), together with
# the cci-src submodule at the commit recorded in that revision, so local changes
# and build outputs are never packaged. <version> is PHP_CUBRID_VERSION of
# php_cubrid_version.h in that revision.
#
# The file name follows the PIE "pre-packaged-source" convention
# (php_<extension>-<version>-src.tgz) and the top directory in the archive has the
# same name, so the archive is used as is by PIE (GitHub release asset of tag
# <version>) and by users who build it by hand (phpize; ./configure; make).
#
# Options
#   --ref REV       revision to package (default: HEAD)
#   --output DIR    output directory (default: script/release)
#   --check [PHP_CONFIG]
#                   build the archive after making it: extract it into
#                   script/release/check and run phpize/configure/make with PHP_CONFIG
#                   the way PIE does (no --with-* option)
#                   (default: php-config in PATH)
#   -h, --help      show this help
#
# Changes applied to the packaged cci-src (the cci-src repository is not changed):
#   - build.sh does not stop when git is not installed (no .git in the archive).
#   - BUILD_NUMBER holds the full CCI version (<major.minor.patch>.<serial>-<hash>)
#     that a build from the git work tree would produce.
#   - Only what the Linux build needs is packaged (win/ is left out).

set -e

# ---- per-driver settings ----------------------------------------------------
ext_name=cubrid
version_header=php_cubrid_version.h
version_macro=PHP_CUBRID_VERSION
# files and directories of the driver to package (relative to the repository root)
driver_files="config.m4 config.w32 php_cubrid8.c php_cubrid8.h php_cubrid_version.h
              composer.json LICENSE README.md release-notes.html build-guide.md
              build.sh"
# -----------------------------------------------------------------------------

# files and directories of cci-src needed by the Linux build
cci_files="BUILD_NUMBER CMakeLists.txt COPYING CREDITS README.md build.sh
           cci cmake external include src tools"
# same as CCI_VERSION_START_DATE of cci-src/cci/CMakeLists.txt
cci_version_start_date=2021-07-14

usage()
{
    sed -n '3,32p' "$0" | sed 's/^# \{0,1\}//'
    exit ${1:-1}
}

info()  { echo "[INFO] $*"; }
warn()  { echo "[WARN] $*" >&2; }
fatal() { echo "[ERROR] $*" >&2; exit 1; }

root_dir=$(cd "$(dirname "$0")/.." && pwd)
ref=HEAD
output_dir=$root_dir/script/release
check=0
check_php_config=""

while [ $# -gt 0 ]; do
    case "$1" in
        --ref) [ -n "$2" ] || usage; ref=$2; shift 2 ;;
        --output) [ -n "$2" ] || usage; output_dir=$2; shift 2 ;;
        --check)
            check=1; shift
            if [ $# -gt 0 ] && [ "${1#-}" = "$1" ]; then check_php_config=$1; shift; fi
            ;;
        -h|--help) usage 0 ;;
        *) usage ;;
    esac
done

for tool in git tar gzip sha256sum; do
    command -v $tool > /dev/null || fatal "$tool not found"
done

cd "$root_dir"
commit=$(git rev-parse --verify -q "$ref^{commit}") || fatal "unknown revision: $ref"

# ---- version ----------------------------------------------------------------
version=$(git show "$commit:$version_header" |
          sed -n "s/^#define $version_macro \"\([^\"]*\)\".*/\1/p")
echo "$version" | grep -Eq '^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$' ||
    fatal "cannot read $version_macro from $version_header (got '$version')"

name=php_${ext_name}-${version}-src
info "driver  : $ext_name $version ($(git rev-parse --short "$commit"), $ref)"

if [ "$ref" = HEAD ] && [ -n "$(git status --porcelain --untracked-files=no)" ]; then
    warn "the work tree has uncommitted changes; they are NOT packaged (HEAD is)"
fi

# PIE looks for the release asset in the GitHub release of the tag <version>.
if tag_commit=$(git rev-parse --verify -q "refs/tags/$version^{commit}"); then
    [ "$tag_commit" = "$commit" ] ||
        warn "tag $version exists but points to $(git rev-parse --short "$tag_commit"), not to $ref"
else
    info "tag $version does not exist yet (PIE needs a GitHub release of tag $version)"
fi

# ---- cci-src ----------------------------------------------------------------
cci_commit=$(git rev-parse "$commit:cci-src" 2> /dev/null) ||
    fatal "cci-src submodule not found in $ref"
[ -e cci-src/.git ] || fatal "cci-src is not checked out (git submodule update --init)"
git -C cci-src cat-file -e "$cci_commit^{commit}" 2> /dev/null ||
    fatal "cci-src commit $cci_commit is not available (git submodule update --init)"

cci_base=$(git -C cci-src show "$cci_commit:BUILD_NUMBER" | head -1 | tr -d '[:space:]')
echo "$cci_base" | grep -Eq '^[0-9]+\.[0-9]+\.[0-9]+' || fatal "invalid cci-src BUILD_NUMBER: $cci_base"
cci_base=$(echo "$cci_base" | cut -d . -f 1-3)
cci_serial=$(printf '%04d' "$(git -C cci-src rev-list --count --after "$cci_version_start_date" "$cci_commit")")
cci_version=$cci_base.$cci_serial-$(git -C cci-src rev-parse --short=7 "$cci_commit")
info "cci-src : $cci_version"

# ---- stage ------------------------------------------------------------------
stage_root=$root_dir/script/release/stage
stage=$stage_root/$name
rm -rf "$stage_root"
mkdir -p "$stage/cci-src"

missing=""
for f in $driver_files; do
    git cat-file -e "$commit:$f" 2> /dev/null || missing="$missing $f"
done
[ -z "$missing" ] || fatal "not committed in $ref:$missing"

# shellcheck disable=SC2086
git archive --format=tar "$commit" -- $driver_files | tar -x -C "$stage"
# shellcheck disable=SC2086
git -C cci-src archive --format=tar "$cci_commit" -- $cci_files | tar -x -C "$stage/cci-src"

# build.sh of cci-src stops when git is not installed, which is not needed
# without .git (the version comes from BUILD_NUMBER).
cci_build=$stage/cci-src/build.sh
grep -q 'print_fatal "Git not found"' "$cci_build" ||
    fatal "git check not found in cci-src/build.sh; update script/release.sh"
sed -i 's/print_fatal "Git not found"/print_info "Git not found (not needed for the source release)"/' "$cci_build"
grep -q 'print_fatal "Git not found"' "$cci_build" && fatal "failed to patch cci-src/build.sh"

echo "$cci_version" > "$stage/cci-src/BUILD_NUMBER"

# ---- archive ----------------------------------------------------------------
mkdir -p "$output_dir"
output_dir=$(cd "$output_dir" && pwd)
archive=$output_dir/$name.tgz
mtime=$(git log -1 --format=%cI "$commit")

tar -C "$stage_root" --sort=name --mtime="$mtime" --owner=0 --group=0 --numeric-owner \
    -cf - "$name" | gzip -9n > "$archive"
(cd "$output_dir" && sha256sum "$name.tgz" > "$name.tgz.sha256")
rm -rf "$stage_root"

info "archive : $archive ($(du -h --apparent-size "$archive" | cut -f1))"
info "sha256  : $(cut -d ' ' -f 1 "$archive.sha256")"

# ---- check ------------------------------------------------------------------
if [ $check -eq 1 ]; then
    if [ -z "$check_php_config" ]; then
        check_php_config=$(command -v php-config || true)
    fi
    [ -x "$check_php_config" ] || fatal "php-config not found (use --check PATH)"
    phpize=$(dirname "$check_php_config")/phpize
    [ -x "$phpize" ] || fatal "phpize not found next to $check_php_config"

    check_dir=$root_dir/script/release/check
    rm -rf "$check_dir"
    mkdir -p "$check_dir"
    tar -xzf "$archive" -C "$check_dir"
    info "check   : building $check_dir/$name with $check_php_config"
    (
        cd "$check_dir/$name"
        "$phpize"
        # no --with-<ext>, as PIE does: the extension is enabled by default
        ./configure --with-php-config="$check_php_config"
        make -j"$(nproc)"
    ) > "$check_dir/build.log" 2>&1 || fatal "build failed, see $check_dir/build.log"

    module=$check_dir/$name/modules/$ext_name.so
    [ -f "$module" ] || fatal "$module not built"
    php_binary=$("$check_php_config" --php-binary)
    loaded=$("$php_binary" -n -d "extension=$module" -r "echo phpversion('$ext_name');") ||
        fatal "$module does not load in $php_binary"
    [ "$loaded" = "$version" ] || warn "$module reports version '$loaded', expected $version"
    info "check   : OK ($module, $ext_name $loaded)"
fi
