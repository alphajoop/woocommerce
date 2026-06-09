#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_MAIN="${ROOT}/woo-lomi.php"
DIST_DIR="${ROOT}/dist"
STAGE_DIR="${DIST_DIR}/woo-lomi"

if [[ ! -f "${PLUGIN_MAIN}" ]]; then
	echo "error: woo-lomi.php not found at ${PLUGIN_MAIN}" >&2
	exit 1
fi

VERSION="$(grep -E "define\s*\(\s*'WC_LOMI_VERSION'" "${PLUGIN_MAIN}" | sed -E "s/.*'([^']+)'.*/\1/" | head -1)"
if [[ -z "${VERSION}" ]]; then
	echo "error: could not read WC_LOMI_VERSION from woo-lomi.php" >&2
	exit 1
fi

ZIP_NAME="woo-lomi-${VERSION}.zip"
ZIP_PATH="${DIST_DIR}/${ZIP_NAME}"
FIXED_ZIP_PATH="${DIST_DIR}/woo-lomi.zip"

rm -rf "${STAGE_DIR}"
mkdir -p "${STAGE_DIR}"

rsync -a \
	--exclude 'node_modules' \
	--exclude 'resources' \
	--exclude '.git' \
	--exclude 'dist' \
	--exclude '*.zip' \
	--exclude 'package-lock.json' \
	--exclude 'pnpm-lock.yaml' \
	--exclude '.DS_Store' \
	"${ROOT}/" "${STAGE_DIR}/"

mkdir -p "${DIST_DIR}"
rm -f "${ZIP_PATH}" "${FIXED_ZIP_PATH}"

(
	cd "${DIST_DIR}"
	zip -r -q "${ZIP_NAME}" woo-lomi
	cp "${ZIP_NAME}" woo-lomi.zip
)

echo "Built ${ZIP_PATH}"
echo "Also copied to ${FIXED_ZIP_PATH}"
