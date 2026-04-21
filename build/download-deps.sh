#!/usr/bin/env bash
set -euo pipefail

# Downloads pre-built JS vendor dependencies from the npm registry via unpkg.
# Update version numbers here when upgrading a dependency.

ICAL_JS_VERSION="1.5.0"
FULLCALENDAR_VERSION="6.1.20"
MARKED_VERSION="17.0.6"
POPPERJS_VERSION="2.11.8"
TIPPY_VERSION="6.3.7"

DEPS=(
  "https://unpkg.com/ical.js@${ICAL_JS_VERSION}/build/ical.min.js public/js/vendor/ical-js.js"
  "https://unpkg.com/fullcalendar@${FULLCALENDAR_VERSION}/index.global.min.js public/js/vendor/fullcalendar.js"
  "https://unpkg.com/@fullcalendar/core@${FULLCALENDAR_VERSION}/locales-all.global.min.js public/js/vendor/fullcalendar-core.js"
  "https://unpkg.com/@fullcalendar/icalendar@${FULLCALENDAR_VERSION}/index.global.min.js public/js/vendor/fullcalendar-icalendar.js"
  "https://unpkg.com/marked@${MARKED_VERSION}/lib/marked.umd.js public/js/vendor/marked.js"
  "https://unpkg.com/@popperjs/core@${POPPERJS_VERSION}/dist/umd/popper.min.js public/js/vendor/popperjs-core.js"
  "https://unpkg.com/tippy.js@${TIPPY_VERSION}/dist/tippy-bundle.umd.min.js public/js/vendor/tippy-js.js"
)

mkdir -p public/js/vendor

for dep in "${DEPS[@]}"; do
  url="${dep%% *}"
  dest="${dep##* }"
  curl -sSL --fail "$url" -o "$dest"
  echo "Downloaded: $url → $dest"
done
