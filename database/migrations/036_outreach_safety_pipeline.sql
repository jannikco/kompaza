-- ============================================
-- MIGRATION 036: Outreach safety pipeline
--
-- The governed write path. Every LinkedIn write -- from MCP, REST, the admin UI,
-- a loadout or a cron automation -- funnels through ActionExecutor, which checks
-- these tables in a fixed order before anything leaves the building:
--
--   idempotency -> account health -> emergency stop -> do-not-engage -> limits
--   -> off-hours -> pacing -> approval mode -> transport -> EXECUTE -> commit
--
-- outreach_actions and outreach_activity_limits are the canonical definitions; the MCP
-- and REST layers read them rather than defining their own.
--
-- Re-runnable.
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------
-- Budgets
-- ----------------------------------------

-- Per-account, per-channel caps. Shared with the cron automations, so an agent
-- and a background automation draw down the SAME budget -- which is the contract
-- LeadShark advertises and the only way the numbers mean anything.
--
-- `inmail` is read-only: it mirrors a paid LinkedIn credit balance, not a cap we set.
CREATE TABLE IF NOT EXISTS outreach_activity_limits (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           INT UNSIGNED NOT NULL,
    linkedin_account_id INT UNSIGNED NOT NULL,
    channel ENUM('dm','connected_reply','non_connected_reply','connection_daily',
                 'connection_weekly','comment','reaction','profile_view',
                 'enrich_daily','inmail') NOT NULL,
    period          ENUM('day','week') NOT NULL DEFAULT 'day',
    limit_value     INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = channel stopped',
    used            INT UNSIGNED NOT NULL DEFAULT 0,
    window_start    DATE NOT NULL,
    is_readonly     TINYINT(1) NOT NULL DEFAULT 0,
    is_paused       TINYINT(1) NOT NULL DEFAULT 0,
    approval_mode   ENUM('auto','queue','off') NOT NULL DEFAULT 'auto',
    cooldown_seconds INT UNSIGNED NOT NULL DEFAULT 0,
    next_action_at  DATETIME DEFAULT NULL COMMENT 'Velocity pacing: earliest next send',
    last_action_at  DATETIME DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_account_channel (linkedin_account_id, channel),
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (linkedin_account_id) REFERENCES linkedin_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lowering a cap applies immediately. RAISING one is staged here for the tenant
-- owner to approve, so an agent can never widen its own budget.
CREATE TABLE IF NOT EXISTS outreach_limit_change_requests (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id            INT UNSIGNED NOT NULL,
    linkedin_account_id  INT UNSIGNED NOT NULL,
    channel              VARCHAR(32) NOT NULL,
    current_value        INT UNSIGNED NOT NULL,
    requested_value      INT UNSIGNED NOT NULL,
    requested_by_user_id INT UNSIGNED DEFAULT NULL,
    requested_via        ENUM('mcp','rest','admin') NOT NULL DEFAULT 'mcp',
    reason               VARCHAR(500) DEFAULT NULL,
    status               ENUM('pending','approved','rejected','expired') NOT NULL DEFAULT 'pending',
    decided_by_user_id   INT UNSIGNED DEFAULT NULL,
    decided_at           DATETIME DEFAULT NULL,
    expires_at           DATETIME NOT NULL,
    created_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pending (tenant_id, status, created_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (linkedin_account_id) REFERENCES linkedin_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------
-- Do-not-engage
-- ----------------------------------------

-- A hard chokepoint, never a warning and never overridable by a request flag.
-- Archiving a lead writes BOTH linkedin_leads.archived_at and a row here, so the
-- block survives deletion of the lead.
CREATE TABLE IF NOT EXISTS outreach_do_not_engage (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id         INT UNSIGNED NOT NULL,
    subject_type      ENUM('person','company','domain') NOT NULL DEFAULT 'person',
    subject_urn       VARCHAR(191) DEFAULT NULL,
    public_identifier VARCHAR(191) DEFAULT NULL,
    email_domain      VARCHAR(191) DEFAULT NULL,
    reason ENUM('manual','archived','opted_out','complaint','bounced','competitor')
           NOT NULL DEFAULT 'manual',
    note               VARCHAR(500) DEFAULT NULL,
    created_by_user_id INT UNSIGNED DEFAULT NULL,
    created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tenant_urn (tenant_id, subject_urn),
    INDEX idx_tenant_public (tenant_id, public_identifier),
    INDEX idx_tenant_domain (tenant_id, email_domain),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------
-- Approval queue
-- ----------------------------------------

-- When a channel's approval_mode is 'queue', the whole ActionRequest is parked
-- here with a human-readable preview of exactly what would be sent. On approval
-- the request is replayed through ActionExecutor, so limits and do-not-engage are
-- re-checked at execution time -- a week may have passed.
CREATE TABLE IF NOT EXISTS outreach_pending_actions (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           INT UNSIGNED NOT NULL,
    linkedin_account_id INT UNSIGNED NOT NULL,
    channel             VARCHAR(32) NOT NULL,
    action_type         VARCHAR(48) NOT NULL,
    subject_urn         VARCHAR(191) DEFAULT NULL,
    subject_label       VARCHAR(255) DEFAULT NULL COMMENT 'e.g. "Jane Doe - VP Sales at Acme"',
    target_urn          VARCHAR(191) DEFAULT NULL,
    payload_json        MEDIUMTEXT NOT NULL COMMENT 'Full replayable ActionRequest',
    preview_text        TEXT DEFAULT NULL COMMENT 'Exactly what will be sent',
    requested_via       ENUM('mcp','rest','admin','automation','loadout') NOT NULL DEFAULT 'mcp',
    requested_by_user_id INT UNSIGNED DEFAULT NULL,
    loadout_run_id      INT UNSIGNED DEFAULT NULL,
    status ENUM('pending','approved','rejected','executed','expired','failed')
           NOT NULL DEFAULT 'pending',
    decided_by_user_id  INT UNSIGNED DEFAULT NULL,
    decided_at          DATETIME DEFAULT NULL,
    reject_reason       VARCHAR(500) DEFAULT NULL,
    action_id           BIGINT UNSIGNED DEFAULT NULL,
    expires_at          DATETIME NOT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status, created_at),
    INDEX idx_account_status (linkedin_account_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (linkedin_account_id) REFERENCES linkedin_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------
-- Action audit log
-- ----------------------------------------

-- Every governed write, whatever its result. Backs the list_actions tool, the
-- admin Action Log, and dedupe ("did I already comment on this post?").
--
-- idempotency_key is sha256(account|action_type|subject|body) and is what makes a
-- retried MCP call return the prior result instead of sending twice.
CREATE TABLE IF NOT EXISTS outreach_actions (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           INT UNSIGNED NOT NULL,
    linkedin_account_id INT UNSIGNED DEFAULT NULL COMMENT 'The acting profile (act_as aware)',
    user_id             INT UNSIGNED DEFAULT NULL,
    source              ENUM('mcp','rest','admin','automation','loadout','cron') NOT NULL DEFAULT 'mcp',
    source_ref          VARCHAR(128) DEFAULT NULL COMMENT 'Tool name / automation id / job id',
    tool_name           VARCHAR(64) DEFAULT NULL,
    request_id          CHAR(32) DEFAULT NULL,
    channel ENUM('dm','inmail','connected_reply','non_connected_reply','connection_daily',
                 'connection_weekly','comment','reaction','profile_view','none')
            NOT NULL DEFAULT 'none',
    action_type         VARCHAR(48) NOT NULL,
    subject_urn         VARCHAR(191) DEFAULT NULL,
    subject_label       VARCHAR(255) DEFAULT NULL,
    target_urn          VARCHAR(191) DEFAULT NULL,
    target_url          VARCHAR(500) DEFAULT NULL,
    body_text           TEXT DEFAULT NULL,
    as_organization_id  VARCHAR(50) DEFAULT NULL,
    lead_id             INT UNSIGNED DEFAULT NULL,
    automation_id       INT UNSIGNED DEFAULT NULL,
    loadout_run_id      INT UNSIGNED DEFAULT NULL,
    pending_action_id   BIGINT UNSIGNED DEFAULT NULL,
    result              ENUM('ok','queued','refused','error') NOT NULL,
    refusal_code        VARCHAR(48) DEFAULT NULL,
    error_message       TEXT DEFAULT NULL,
    response_urn        VARCHAR(191) DEFAULT NULL COMMENT 'Created comment/message urn',
    endpoint_key        VARCHAR(64) DEFAULT NULL,
    execution_mode      ENUM('server','relay') DEFAULT NULL,
    http_status         SMALLINT UNSIGNED DEFAULT NULL,
    duration_ms         INT UNSIGNED DEFAULT NULL,
    idempotency_key     CHAR(64) DEFAULT NULL,
    request_payload     JSON DEFAULT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_idempotency (idempotency_key),
    INDEX idx_tenant_created (tenant_id, created_at),
    INDEX idx_account_channel (linkedin_account_id, channel, created_at),
    INDEX idx_result (tenant_id, result, created_at),
    INDEX idx_dedupe (tenant_id, action_type, subject_urn),
    INDEX idx_lead (tenant_id, lead_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------
-- Job queue
-- ----------------------------------------

-- Claimed with FOR UPDATE SKIP LOCKED (MariaDB 10.6+), so several worker slots can
-- run concurrently without the one-job-at-a-time limitation of the transcoder.
--
-- dedupe_key must be set to NULL when a job reaches done/failed, so the same
-- recurring job can be enqueued again -- UNIQUE permits unlimited NULLs.
CREATE TABLE IF NOT EXISTS outreach_jobs (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           INT UNSIGNED NOT NULL,
    linkedin_account_id INT UNSIGNED DEFAULT NULL,
    type                VARCHAR(48) NOT NULL,
    payload             MEDIUMTEXT DEFAULT NULL COMMENT 'JSON',
    dedupe_key          VARCHAR(191) DEFAULT NULL,
    priority            TINYINT UNSIGNED NOT NULL DEFAULT 5 COMMENT '1 = highest',
    status              ENUM('pending','running','done','failed','cancelled') NOT NULL DEFAULT 'pending',
    attempts            INT UNSIGNED NOT NULL DEFAULT 0,
    max_attempts        INT UNSIGNED NOT NULL DEFAULT 5,
    run_after           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    locked_at           DATETIME DEFAULT NULL,
    locked_by           VARCHAR(64) DEFAULT NULL,
    last_error          TEXT DEFAULT NULL,
    started_at          DATETIME DEFAULT NULL,
    completed_at        DATETIME DEFAULT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_dedupe (dedupe_key),
    INDEX idx_claim (status, run_after, priority, id),
    INDEX idx_tenant_type (tenant_id, type, status),
    INDEX idx_stale (status, locked_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
