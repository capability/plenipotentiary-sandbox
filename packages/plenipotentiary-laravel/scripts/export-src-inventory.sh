#!/usr/bin/env bash
set -euo pipefail

# Always run from package root
cd "$(dirname "$0")/.."

OUT="${1:-pleni-inventory.md}"

# Add docs-site and .pnpm-store to the ignore list
IGNORE='vendor|node_modules|storage|bootstrap|.git|.idea|.vscode|dist|build|coverage|.phpunit.cache|docs-site|.pnpm-store'

echo "# Plenipotentiary Inventory" > "$OUT"
echo >> "$OUT"

echo "## Folder tree" >> "$OUT"
echo >> "$OUT"
tree -a -I "$IGNORE" -n >> "$OUT" || true
echo >> "$OUT"

echo "## PHP classes & methods" >> "$OUT"
echo >> "$OUT"

rg --hidden --no-ignore \
   -g '!vendor' -g '!node_modules' -g '!*Tests*' -g '!storage' \
   -g '!docs-site' -g '!.pnpm-store' \
   -n --heading \
   -e '^\s*namespace\s+[^;]+;' \
   -e '^\s*(final\s+)?(abstract\s+)?(class|interface|trait)\s+\w+' \
   -e '^\s*(public|protected|private)\s+(static\s+)?function\s+\w+\s*\([^)]*\)' \
   -- 'src/**/*.php' 'app/**/*.php' 'packages/**/*.php' >> "$OUT" || true

echo >> "$OUT"
echo "Generated on: $(date -Iseconds)" >> "$OUT"
echo "Wrote $OUT"

