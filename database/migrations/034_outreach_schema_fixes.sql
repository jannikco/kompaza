-- ============================================
-- MIGRATION 034: Outreach schema fixes
--
-- Reconciles linkedin_accounts and linkedin_leads with the code that writes them,
-- and adds the columns the Outreach agent surface needs on both.
--
-- Fixes shipped here:
--   B1  linkedin_accounts has no `updated_at`, but account-store.php writes it on
--       BOTH the INSERT and the UPDATE path. Combined with the missing user_id in
--       the INSERT (fixed in the controller), connecting a LinkedIn account fatals
--       to a blank page in production today.
--   B3  connectpilot-post-runner.php writes connection_status='connected', which is
--       not in the ENUM. The real concept is network distance, so model it properly
--       rather than overloading invite state.
--   B10 Models/LinkedInLead.php reads/writes columns that do not exist. The ones that
--       are genuine missing CRM fields (status, notes, industry, connection_degree)
--       are added here; full_name / linkedin_url / converted_customer_id are handled
--       with SELECT aliases in the model instead, so nothing denormalises.
--
-- Re-runnable. MariaDB 11.8 (prod) supports ADD COLUMN/INDEX IF NOT EXISTS.
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------
-- linkedin_accounts
-- ----------------------------------------

-- B1: the column account-store.php has always written but never existed.
ALTER TABLE linkedin_accounts
    ADD COLUMN IF NOT EXISTS updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Encryption at rest. Plaintext li_at_cookie / csrf_token are dropped in 046,
-- by hand, only after scripts/outreach-encrypt-cookies.php reports 0 unencrypted rows.
ALTER TABLE linkedin_accounts
    ADD COLUMN IF NOT EXISTS li_at_cookie_enc   BLOB DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS csrf_token_enc     BLOB DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS encryption_version TINYINT UNSIGNED NOT NULL DEFAULT 0;

-- Identity, captured by the Chrome extension on session sync.
ALTER TABLE linkedin_accounts
    ADD COLUMN IF NOT EXISTS member_urn        VARCHAR(191) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS public_identifier VARCHAR(191) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS profile_urn       VARCHAR(191) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS avatar_url        VARCHAR(500) DEFAULT NULL;

-- Browser fingerprint mirror. Replaces the hardcoded x-li-track / User-Agent in
-- LinkedInService.php, which is pinned to clientVersion 1.13.8857 and will rot.
ALTER TABLE linkedin_accounts
    ADD COLUMN IF NOT EXISTS user_agent             VARCHAR(512) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS accept_language        VARCHAR(191) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS li_track_json          TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS sec_ch_ua              VARCHAR(512) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS sec_ch_ua_platform     VARCHAR(64)  DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS timezone               VARCHAR(64)  NOT NULL DEFAULT 'UTC',
    ADD COLUMN IF NOT EXISTS fingerprint_updated_at DATETIME DEFAULT NULL;

-- Session lifecycle + execution mode.
-- B2: nothing currently sets status='cookie_expired' or writes cookie_expires_at.
ALTER TABLE linkedin_accounts
    ADD COLUMN IF NOT EXISTS session_source       ENUM('manual','extension') NOT NULL DEFAULT 'manual',
    ADD COLUMN IF NOT EXISTS session_synced_at    DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS last_auth_failure_at DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS auth_failure_count   INT UNSIGNED NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS read_mode            ENUM('server','relay') NOT NULL DEFAULT 'server',
    ADD COLUMN IF NOT EXISTS write_mode           ENUM('server','relay') NOT NULL DEFAULT 'relay',
    ADD COLUMN IF NOT EXISTS allow_server_write_fallback TINYINT(1) NOT NULL DEFAULT 0;

-- Safety / pacing / governance.
ALTER TABLE linkedin_accounts
    ADD COLUMN IF NOT EXISTS safety_mode          ENUM('auto','queue','read_only') NOT NULL DEFAULT 'queue',
    ADD COLUMN IF NOT EXISTS working_hours_json   JSON DEFAULT NULL
        COMMENT '{"mon":[9,17],...,"sat":null,"sun":null} in the account timezone',
    ADD COLUMN IF NOT EXISTS respect_off_hours    TINYINT(1) NOT NULL DEFAULT 1,
    ADD COLUMN IF NOT EXISTS emergency_stop       TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS emergency_stopped_at DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS emergency_stopped_by INT UNSIGNED DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS inmail_credits       INT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS inmail_credits_checked_at DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS enrich_used_today    INT UNSIGNED NOT NULL DEFAULT 0;

ALTER TABLE linkedin_accounts
    ADD INDEX IF NOT EXISTS idx_tenant_status (tenant_id, status);

-- ----------------------------------------
-- linkedin_leads
-- ----------------------------------------

-- linkedin_id holds a URN; widen it and index it. 191 keeps the utf8mb4 index
-- prefix inside every limit (191 * 4 = 764 bytes).
ALTER TABLE linkedin_leads
    MODIFY COLUMN linkedin_id VARCHAR(191) DEFAULT NULL;

-- B3: 'withdrawn' is a real invite outcome the runner needs; 'connected' is NOT
-- added, because that is network distance, not invite state (see network_distance).
ALTER TABLE linkedin_leads
    MODIFY COLUMN connection_status ENUM('none','pending','accepted','rejected','withdrawn') DEFAULT 'none';

-- B10: the genuinely-missing CRM fields the model and views already reference.
-- `status` is the CRM lifecycle and is deliberately distinct from connection_status,
-- which tracks the LinkedIn relationship.
ALTER TABLE linkedin_leads
    ADD COLUMN IF NOT EXISTS status ENUM('new','contacted','connected','responded','converted','rejected','archived')
        NOT NULL DEFAULT 'new' AFTER connection_status,
    ADD COLUMN IF NOT EXISTS network_distance ENUM('SELF','DISTANCE_1','DISTANCE_2','DISTANCE_3','OUT_OF_NETWORK')
        DEFAULT NULL AFTER status,
    ADD COLUMN IF NOT EXISTS connection_degree VARCHAR(10)  DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS industry          VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS notes             TEXT         DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS linkedin_username VARCHAR(191) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS member_urn        VARCHAR(191) DEFAULT NULL;

-- Outreach provenance + intent.
ALTER TABLE linkedin_leads
    ADD COLUMN IF NOT EXISTS source ENUM('campaign','automation','page','link','manual','signal','invitation','import')
        NOT NULL DEFAULT 'manual',
    ADD COLUMN IF NOT EXISTS heat_score        DECIMAL(6,2) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS do_not_engage     TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS archived_at       DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS email_captured_at DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS enriched_at       DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS enrichment        JSON DEFAULT NULL;

-- Relationship summary, denormalised so get_lead_activity is a single row read.
-- Maintained exclusively by OutreachLeadEvent::record(), which writes the ledger row
-- and flips the matching flag in one transaction.
ALTER TABLE linkedin_leads
    ADD COLUMN IF NOT EXISTS has_history                TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS engaged_your_content       TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS checked_resource           TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS submitted_email            TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS we_dmed                    TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS we_replied_to_comment      TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS we_sent_connection_request TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS first_touch_at DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS last_event_at  DATETIME DEFAULT NULL;

ALTER TABLE linkedin_leads
    ADD INDEX IF NOT EXISTS idx_tenant_heat     (tenant_id, heat_score),
    ADD INDEX IF NOT EXISTS idx_tenant_lifecycle(tenant_id, status),
    ADD INDEX IF NOT EXISTS idx_tenant_dne      (tenant_id, do_not_engage),
    ADD INDEX IF NOT EXISTS idx_tenant_member   (tenant_id, member_urn),
    ADD INDEX IF NOT EXISTS idx_tenant_linkedin (tenant_id, linkedin_id);

-- ----------------------------------------
-- Backfills
-- ----------------------------------------

-- Vanity name out of the stored profile URL, used to resolve people by slug.
UPDATE linkedin_leads
   SET linkedin_username = SUBSTRING_INDEX(SUBSTRING_INDEX(linkedin_profile_url, '/in/', -1), '/', 1)
 WHERE linkedin_username IS NULL
   AND linkedin_profile_url LIKE '%/in/%';

-- linkedin_id already holds the member URN for rows written by the post runner.
UPDATE linkedin_leads
   SET member_urn = linkedin_id
 WHERE member_urn IS NULL
   AND linkedin_id LIKE 'urn:li:%';

-- B3 repair: rows the post runner wrote before the ENUM was corrected. Under a
-- non-strict sql_mode the invalid 'connected' value silently became ''.
UPDATE linkedin_leads
   SET connection_status = 'accepted'
 WHERE connection_status = '';

-- Seed the CRM lifecycle from the state we already have. Order matters: later
-- statements win, so this runs least-advanced to most-advanced.
UPDATE linkedin_leads SET status = 'connected' WHERE connection_status = 'accepted'   AND status = 'new';
UPDATE linkedin_leads SET status = 'contacted' WHERE last_contacted_at IS NOT NULL    AND status = 'new';
UPDATE linkedin_leads SET status = 'responded' WHERE last_replied_at   IS NOT NULL;
UPDATE linkedin_leads SET status = 'converted' WHERE customer_id       IS NOT NULL;
UPDATE linkedin_leads SET status = 'archived'  WHERE archived_at       IS NOT NULL;

SET FOREIGN_KEY_CHECKS = 1;
