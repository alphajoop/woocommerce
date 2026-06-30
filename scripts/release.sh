#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_MAIN="${ROOT}/woo-lomi.php"
DIST_DIR="${ROOT}/dist"
STAGE_DIR="${DIST_DIR}/woo-lomi"

EXCLUDES=(
	'node_modules'
	'resources'
	'.git'
	'dist'
	'*.zip'
	'package-lock.json'
	'pnpm-lock.yaml'
	'bun.lock'
	'bun.lockb'
	'.DS_Store'
)

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

stage_plugin() {
	rm -rf "${STAGE_DIR}"
	mkdir -p "${STAGE_DIR}"

	if command -v rsync >/dev/null 2>&1; then
		local -a rsync_excludes=()
		for pattern in "${EXCLUDES[@]}"; do
			rsync_excludes+=(--exclude "${pattern}")
		done
		if rsync -a "${rsync_excludes[@]}" "${ROOT}/" "${STAGE_DIR}/" 2>/dev/null; then
			return
		fi
		echo "rsync unavailable — falling back to tar" >&2
		rm -rf "${STAGE_DIR}"
		mkdir -p "${STAGE_DIR}"
	fi

	if command -v tar >/dev/null 2>&1; then
		local -a tar_excludes=()
		for pattern in "${EXCLUDES[@]}"; do
			tar_excludes+=(--exclude="./${pattern}")
		done

		(
			cd "${ROOT}"
			tar cf - "${tar_excludes[@]}" .
		) | (
			cd "${STAGE_DIR}"
			tar xf -
		)
		return
	fi

	echo "error: need rsync or tar to stage the plugin for release" >&2
	exit 1
}

create_zip() {
	mkdir -p "${DIST_DIR}"
	rm -f "${ZIP_PATH}" "${FIXED_ZIP_PATH}"

	if command -v zip >/dev/null 2>&1; then
		(
			cd "${DIST_DIR}"
			zip -r -q "${ZIP_NAME}" woo-lomi
		)
	elif command -v powershell.exe >/dev/null 2>&1; then
		local stage_win zip_win
		stage_win="$(cygpath -w "${STAGE_DIR}")"
		zip_win="$(cygpath -w "${ZIP_PATH}")"
		powershell.exe -NoProfile -Command "Compress-Archive -Path '${stage_win}' -DestinationPath '${zip_win}' -Force"
	else
		echo "error: need zip or powershell to create the release archive" >&2
		exit 1
	fi

	cp "${ZIP_PATH}" "${FIXED_ZIP_PATH}"
}

stage_plugin
create_zip

echo "Built ${ZIP_PATH}"
echo "Also copied to ${FIXED_ZIP_PATH}"
