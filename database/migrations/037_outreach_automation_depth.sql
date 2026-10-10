-- ============================================
-- MIGRATION 037: Outreach automation depth
--
-- Leadshark Pro+ parity for comment-to-DM automations:
-- multiple keywords, auto-like, auto-connect, follow-up DMs,
-- draft status, per-comment tracking, and the lead-event ledger
-- that get_lead_activity / list_signals will read.
--
-- Also seeds default outreach_activity_limits for existing accounts
-- so ActionExecutor and the cron runners share one budget.
--
-- Re-runnable.
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------
-- Automations
-- ----------------------------------------

ALTER TABLE connectpilot_post_automations
    MODIFY COLUMN status ENUM('draft','active','paused','completed') NOT NULL DEFAULT 'draft';

ALTER TABLE connectpilot_post_automations
    ADD COLUMN IF NOT EXISTS trigger_keywords JSON DEFAULT NULL
        COMMENT '["GUIDE","interested"] — trigger_keyword stays the first/display value' AFTER trigger_keyword,
    ADD COLUMN IF NOT EXISTS auto_like_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER auto_dm_enabled,
    ADD COLUMN IF NOT EXISTS auto_connect_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER auto_like_enabled,
    ADD COLUMN IF NOT EXISTS partially_engage TINYINT(1) NOT NULL DEFAULT 0
        COMMENT 'Skip comments that already have a reply from us' AFTER auto_connect_enabled,
    ADD COLUMN IF NOT EXISTS non_first_degree_reply_template TEXT DEFAULT NULL AFTER auto_reply_template,
    ADD COLUMN IF NOT EXISTS dm_templates JSON DEFAULT NULL
        COMMENT 'Rotating DM bodies; dm_template is the fallback / first' AFTER dm_template,
    ADD COLUMN IF NOT EXISTS follow_up_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER dm_templates,
    ADD COLUMN IF NOT EXISTS follow_up_template TEXT DEFAULT NULL AFTER follow_up_enabled,
    ADD COLUMN IF NOT EXISTS follow_up_delay_minutes INT UNSIGNED NOT NULL DEFAULT 120 AFTER follow_up_template,
    ADD COLUMN IF NOT EXISTS follow_up_only_if_no_response TINYINT(1) NOT NULL DEFAULT 1 AFTER follow_up_delay_minutes,
    ADD COLUMN IF NOT EXISTS likes_sent INT UNSIGNED NOT NULL DEFAULT 0 AFTER leads_captured,
    ADD COLUMN IF NOT EXISTS connections_sent INT UNSIGNED NOT NULL DEFAULT 0 AFTER likes_sent,
    ADD COLUMN IF NOT EXISTS follow_ups_sent INT UNSIGNED NOT NULL DEFAULT 0 AFTER connections_sent;

-- Existing rows: copy the single keyword into the new list so the runner
-- does not see an empty trigger after deploy.
UPDATE connectpilot_post_automations
   SET trigger_keywords = JSON_ARRAY(trigger_keyword)
 WHERE trigger_keywords IS NULL
   AND trigger_keyword IS NOT NULL
   AND trigger_keyword <> '';

-- ----------------------------------------
-- Per-comment tracking
-- ----------------------------------------

ALTER TABLE connectpilot_post_comments
    ADD COLUMN IF NOT EXISTS commenter_distance VARCHAR(32) DEFAULT NULL AFTER commenter_headline,
    ADD COLUMN IF NOT EXISTS matched_keyword VARCHAR(100) DEFAULT NULL AFTER keyword_matched,
    ADD COLUMN IF NOT EXISTS like_sent TINYINT(1) NOT NULL DEFAULT 0 AFTER reply_sent_at,
    ADD COLUMN IF NOT EXISTS like_sent_at DATETIME DEFAULT NULL AFTER like_sent,
    ADD COLUMN IF NOT EXISTS connect_sent TINYINT(1) NOT NULL DEFAULT 0 AFTER like_sent_at,
    ADD COLUMN IF NOT EXISTS connect_sent_at DATETIME DEFAULT NULL AFTER connect_sent,
    ADD COLUMN IF NOT EXISTS follow_up_sent TINYINT(1) NOT NULL DEFAULT 0 AFTER dm_sent_at,
    ADD COLUMN IF NOT EXISTS follow_up_sent_at DATETIME DEFAULT NULL AFTER follow_up_sent,
    ADD COLUMN IF NOT EXISTS follow_up_due_at DATETIME DEFAULT NULL AFTER follow_up_sent_at,
    ADD COLUMN IF NOT EXISTS skipped_reason VARCHAR(48) DEFAULT NULL AFTER follow_up_due_at;

ALTER TABLE connectpilot_post_comments
    ADD INDEX IF NOT EXISTS idx_follow_up_due (automation_id, follow_up_due_at, follow_up_sent),
    ADD INDEX IF NOT EXISTS idx_pending_like (automation_id, like_sent),
    ADD INDEX IF NOT EXISTS idx_pending_connect (automation_id, keyword_matched, connect_sent, dm_sent);

-- ----------------------------------------
-- Lead event ledger (get_lead_activity / heat)
-- ----------------------------------------

CREATE TABLE IF NOT EXISTS outreach_lead_events (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           INT UNSIGNED NOT NULL,
    lead_id             INT UNSIGNED DEFAULT NULL,
    linkedin_account_id INT UNSIGNED DEFAULT NULL,
    subject_urn         VARCHAR(191) DEFAULT NULL,
    event_type          VARCHAR(48) NOT NULL,
    weight              DECIMAL(6,2) NOT NULL DEFAULT 0,
    source              VARCHAR(32) DEFAULT NULL,
    source_ref          VARCHAR(128) DEFAULT NULL,
    action_id           BIGINT UNSIGNED DEFAULT NULL,
    payload_json        JSON DEFAULT NULL,
    occurred_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_lead (tenant_id, lead_id, occurred_at),
    INDEX idx_tenant_urn (tenant_id, subject_urn, occurred_at),
    INDEX idx_type (tenant_id, event_type, occurred_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------
-- Default channel budgets for accounts that have none yet
-- ----------------------------------------

INSERT INTO outreach_activity_limits
    (tenant_id, linkedin_account_id, channel, period, limit_value, used, window_start, is_readonly, is_paused, approval_mode, cooldown_seconds)
SELECT la.tenant_id, la.id, v.channel, v.period, v.limit_value, 0, CURDATE(), v.is_readonly, 0, 'auto', v.cooldown
  FROM linkedin_accounts la
  JOIN (
        SELECT 'dm' AS channel, 'day' AS period, 50 AS limit_value, 0 AS is_readonly, 8 AS cooldown
        UNION ALL SELECT 'connected_reply',     'day',  50, 0, 5
        UNION ALL SELECT 'non_connected_reply', 'day',  30, 0, 8
        UNION ALL SELECT 'connection_daily',    'day',  20, 0, 15
        UNION ALL SELECT 'connection_weekly',   'week', 80, 0, 15
        UNION ALL SELECT 'comment',             'day',  40, 0, 5
        UNION ALL SELECT 'reaction',            'day',  80, 0, 3
        UNION ALL SELECT 'profile_view',        'day',  80, 0, 4
        UNION ALL SELECT 'enrich_daily',        'day',  80, 0, 2
        UNION ALL SELECT 'inmail',              'day',   0, 1, 0
  ) v
  LEFT JOIN outreach_activity_limits existing
    ON existing.linkedin_account_id = la.id AND existing.channel = v.channel
 WHERE existing.id IS NULL;

-- Honour whatever the tenant already set on the account row.
UPDATE outreach_activity_limits l
  JOIN linkedin_accounts a ON a.id = l.linkedin_account_id
   SET l.limit_value = a.daily_message_limit
 WHERE l.channel = 'dm'
   AND a.daily_message_limit IS NOT NULL;

UPDATE outreach_activity_limits l
  JOIN linkedin_accounts a ON a.id = l.linkedin_account_id
   SET l.limit_value = a.daily_connection_limit
 WHERE l.channel = 'connection_daily'
   AND a.daily_connection_limit IS NOT NULL;

SET FOREIGN_KEY_CHECKS = 1;
