#!/usr/bin/env bash
#
# Apply the launch migrations to the Kompaza database. Run ONCE on the server,
# right after `git pull` brings the new code in.
#
#   bash scripts/apply-launch-migrations.sh [db_name]   # defaults to "kompaza"
#
# By default it uses the server's mysql auth (run as root). To pass explicit
# credentials, set MYSQL_OPTS, e.g.:
#   MYSQL_OPTS="-u root -psecret" bash scripts/apply-launch-migrations.sh kompaza
#
# 020a is defensive (checks information_schema; safe to re-run). 021-028 create the
# new feature tables and add columns. 024 is intentionally excluded — it is already
# applied in production.
#
set -uo pipefail

DB="${1:-kompaza}"
cd "$(dirname "$0")/.."

MIGRATIONS=(
  database/migrations/020a_money_path_schema.sql
  database/migrations/021_revenue_boosters.sql
  database/migrations/022_sales_infrastructure.sql
  database/migrations/023_funnel_marketing.sql
  database/migrations/025_membership_plans.sql
  database/migrations/026_prompt_library.sql
  database/migrations/027_community.sql
  database/migrations/028_live_sessions.sql
)

echo "Applying launch migrations to database '$DB'..."
for f in "${MIGRATIONS[@]}"; do
  if [ ! -f "$f" ]; then
    echo "!! Missing $f" >&2; exit 1
  fi
  echo "==> $(basename "$f")"
  if ! mysql ${MYSQL_OPTS:-} "$DB" < "$f"; then
    echo "!! FAILED on $(basename "$f"). Fix the cause and re-run; already-applied" >&2
    echo "   files before this one are harmless to skip (020a is idempotent)." >&2
    exit 1
  fi
done

echo "Done. All launch migrations applied to '$DB'."
