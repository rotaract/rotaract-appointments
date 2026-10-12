#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 1 ]]; then
  echo "Usage: $0 <path-to-copylist>" >&2
  exit 1
fi

COPYLIST="$1"

if [[ ! -f "$COPYLIST" ]]; then
  echo "Error: copy list '$COPYLIST' not found." >&2
  exit 2
fi

# Expect filenames like: copylist-public, copylist-admin, etc.
BASENAME=$(basename "$COPYLIST")
if [[ "$BASENAME" =~ ^copylist-(.+)$ ]]; then
  TARGET_FOLDER="${BASH_REMATCH[1]}"
else
  echo "Error: copy list filename must follow the format 'copylist-<folder>'." >&2
  exit 3
fi

echo "Copying files listed in '$COPYLIST' to '${TARGET_FOLDER}/{js,css}/vendor' …"

# Each line: <source> [<destination filename>]
# Without an explicit destination the filename is derived from the package name.
MISSING=0
while read -r SRC NAME; do
  [[ -z "$SRC" ]] && continue  # skip empty lines
  PART1=$(echo "$SRC" | awk -F'/' '{print $2}')
  PART2=$(echo "$SRC" | awk -F'/' '{print $3}')

  if [[ "$PART1" == @* ]]; then
    PKG=$(echo "${PART1#@}-${PART2}" | tr '.' '-')
  else
    PKG=$(echo "$PART1" | tr '.' '-')
  fi

  NAME="${NAME:-${PKG}.${SRC##*.}}"
  if [[ "$NAME" == *.css ]]; then
    TARGET_DIR="${TARGET_FOLDER}/css/vendor"
  else
    TARGET_DIR="${TARGET_FOLDER}/js/vendor"
  fi
  mkdir -p "$TARGET_DIR"
  DEST="${TARGET_DIR}/${NAME}"

  if [[ -f "$SRC" ]]; then
    cp "$SRC" "$DEST"
    echo "Copied $SRC → $DEST"
  else
    echo "Error: source file not found: $SRC" >&2
    MISSING=1
  fi
done < "$COPYLIST"

exit "$MISSING"
