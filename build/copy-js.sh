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

TARGET_DIR="${TARGET_FOLDER}/js/vendor"
mkdir -p "$TARGET_DIR"

echo "Copying files listed in '$COPYLIST' to '$TARGET_DIR' …"

while IFS= read -r SRC; do
  [[ -z "$SRC" ]] && continue  # skip empty lines
  PART1=$(echo "$SRC" | awk -F'/' '{print $2}')
  PART2=$(echo "$SRC" | awk -F'/' '{print $3}')

  if [[ "$PART1" == @* ]]; then
    PKG=$(echo "${PART1#@}-${PART2}" | tr '.' '-')
  else
    PKG=$(echo "$PART1" | tr '.' '-')
  fi

  EXT="${SRC##*.}"
  DEST="${TARGET_DIR}/${PKG}.${EXT}"

  if [[ -f "$SRC" ]]; then
    cp "$SRC" "$DEST"
    echo "Copied $SRC → $DEST"
  else
    echo "Warning: source file not found: $SRC" >&2
  fi
done < "$COPYLIST"
