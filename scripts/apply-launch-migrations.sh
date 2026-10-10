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
# 034-036 are the Outreach agent platform. Every one is re-runnable (CREATE TABLE IF NOT
# EXISTS / ADD COLUMN IF NOT EXISTS, which MariaDB 11.8 supports). 034 also repairs
# the linkedin_accounts/linkedin_leads drift that currently makes connecting a
# LinkedIn account fail outright.
#
# 037 adds comment-to-DM depth (keywords, auto-like/connect, follow-ups, lead events)
# and seeds outreach_activity_limits for existing accounts. Requires 036.
#
# 036a DELETES duplicate linkedin_leads rows (the post runner used to INSERT a lead on
# every DM with no lookup, so one person could hold several rows). It coalesces every
# field onto the surviving row first — including do_not_engage, which is sticky — and
# repoints comments and activity at it. It is idempotent, but it is the one file here
# that removes data: take the mysqldump seriously before running this.
#
# 046_outreach_drop_plaintext_cookies.sql is deliberately NOT listed: it is destructive
# and must be run by hand, only after scripts/outreach-encrypt-cookies.php reports zero
# rows left with encryption_version = 0.
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
  database/migrations/029_membership_events_email_sent.sql
  database/migrations/030_platform_billing_tables.sql
  database/migrations/031_onboarding_json.sql
  database/migrations/032_jannikhansen_tenant.sql
  database/migrations/033_ebook_purchases.sql
  database/migrations/034_outreach_schema_fixes.sql
  database/migrations/035_outreach_linkedin_core.sql
  database/migrations/036_outreach_safety_pipeline.sql
  database/migrations/036a_outreach_lead_dedupe.sql
  database/migrations/037_outreach_automation_depth.sql
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
