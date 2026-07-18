#!/usr/bin/env bash
# Copy jannikhansen.com public assets into Kompaza tenant uploads.
# Run on app2 as root.
#
#   bash scripts/import-jannikhansen-assets.sh [tenant_id]
#
set -euo pipefail

TENANT_ID="${1:-}"
if [ -z "$TENANT_ID" ]; then
  TENANT_ID=$(mysql -N kompaza -e "SELECT id FROM tenants WHERE slug='jannikhansen' LIMIT 1")
fi
if [ -z "$TENANT_ID" ]; then
  echo "ERROR: jannikhansen tenant not found" >&2
  exit 1
fi

SRC_IMG="/var/www/html/jannikhansen.com/public/img"
SRC_PDF="/var/www/html/jannikhansen.com/storage/books"
DEST_BASE="/var/www/kompaza.com/public/uploads/${TENANT_ID}"
DEST_PDF="/var/www/kompaza.com/storage/pdfs/${TENANT_ID}"

echo "Tenant ID: $TENANT_ID"
mkdir -p "$DEST_BASE/img" "$DEST_PDF"

if [ -d "$SRC_IMG" ]; then
  echo "Syncing images from $SRC_IMG ..."
  rsync -a --info=stats2 "$SRC_IMG/" "$DEST_BASE/img/"
else
  echo "WARN: $SRC_IMG missing"
fi

if [ -d "$SRC_PDF" ]; then
  echo "Syncing PDFs from $SRC_PDF ..."
  # Phase 0: copy a few flagship PDFs; full catalog can re-run later
  rsync -a --include='*.pdf' --exclude='*' "$SRC_PDF/" "$DEST_PDF/" 2>/dev/null || \
    cp -n "$SRC_PDF"/*.pdf "$DEST_PDF/" 2>/dev/null || true
  # Prefer EN copies for seed ebooks if both exist
  ls "$DEST_PDF" | head -20
else
  echo "WARN: $SRC_PDF missing"
fi

chown -R www-data:www-data "$DEST_BASE" "$DEST_PDF" 2>/dev/null || true
echo "Assets ready under $DEST_BASE and $DEST_PDF"
