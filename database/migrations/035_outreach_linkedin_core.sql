-- ============================================
-- MIGRATION 035: Outreach LinkedIn core
--
-- Chrome-extension pairing, device tokens, the patchable Voyager endpoint
-- registry, and the browser-relay request queue.
--
-- The endpoint registry is the answer to Voyager's rotating GraphQL queryIds:
-- endpoints live in the DB, the extension harvests the live queryId and
-- clientVersion from the user's own LinkedIn session, and a job promotes them.
-- A broken endpoint becomes one UPDATE, not an emergency deploy.
--
-- Re-runnable.
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------
-- Device pairing
-- ----------------------------------------

-- Short-lived code minted in the admin UI and read by the extension's content
-- script, so the user never pastes anything. Only the hash is stored.
CREATE TABLE IF NOT EXISTS outreach_pairing_codes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    code_hash   CHAR(64) NOT NULL,
    code_prefix VARCHAR(8) NOT NULL COMMENT 'First chars, for display only',
    expires_at  DATETIME NOT NULL,
    consumed_at DATETIME DEFAULT NULL,
    consumed_ip VARCHAR(45) DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_code_hash (code_hash),
    INDEX idx_tenant_expiry (tenant_id, expires_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- A paired browser. The device token is a bearer credential: only sha256 is
-- stored, it is revocable from the admin UI, and last_seen_ip is recorded.
CREATE TABLE IF NOT EXISTS outreach_devices (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           INT UNSIGNED NOT NULL,
    user_id             INT UNSIGNED NOT NULL,
    linkedin_account_id INT UNSIGNED DEFAULT NULL,
    token_hash          CHAR(64) NOT NULL,
    token_prefix        VARCHAR(16) NOT NULL,
    label               VARCHAR(128) DEFAULT NULL,
    extension_version   VARCHAR(32) DEFAULT NULL,
    browser_version     VARCHAR(64) DEFAULT NULL,
    platform            VARCHAR(64) DEFAULT NULL,
    relay_enabled       TINYINT(1) NOT NULL DEFAULT 1,
    last_seen_at        DATETIME DEFAULT NULL,
    last_seen_ip        VARCHAR(45) DEFAULT NULL,
    revoked_at          DATETIME DEFAULT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_token_hash (token_hash),
    INDEX idx_tenant (tenant_id),
    INDEX idx_account_alive (linkedin_account_id, revoked_at, last_seen_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (linkedin_account_id) REFERENCES linkedin_accounts(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------
-- Voyager endpoint registry
-- ----------------------------------------

-- One row per logical endpoint. Seeded from src/Config/linkedin-endpoints.php,
-- but the DB always wins so a break is fixed without a deploy.
CREATE TABLE IF NOT EXISTS linkedin_endpoints (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    endpoint_key       VARCHAR(64) NOT NULL COMMENT 'e.g. search.people',
    transport          ENUM('rest','graphql','messaging_graphql','upload') NOT NULL DEFAULT 'rest',
    method             ENUM('GET','POST','PUT','DELETE','PATCH') NOT NULL DEFAULT 'GET',
    path               VARCHAR(512) NOT NULL,
    fallback_path      VARCHAR(512) DEFAULT NULL COMMENT 'Legacy REST path to try when the primary 404s',
    query_id           VARCHAR(191) DEFAULT NULL COMMENT 'GraphQL queryId, harvested from a live session',
    variables_template TEXT DEFAULT NULL COMMENT 'Restli tuple with {tokens}',
    query_template     TEXT DEFAULT NULL COMMENT 'Query string with {tokens}',
    body_template      TEXT DEFAULT NULL COMMENT 'JSON with {tokens}',
    extra_headers      JSON DEFAULT NULL,
    shape              VARCHAR(64) NOT NULL DEFAULT 'generic' COMMENT 'Normalizer selector',
    risk_level         ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    is_write           TINYINT(1) NOT NULL DEFAULT 0,
    source             ENUM('seed','harvested','manual') NOT NULL DEFAULT 'seed',
    enabled            TINYINT(1) NOT NULL DEFAULT 1,
    version            INT UNSIGNED NOT NULL DEFAULT 1,
    last_ok_at         DATETIME DEFAULT NULL,
    last_error_at      DATETIME DEFAULT NULL,
    last_error         TEXT DEFAULT NULL,
    consecutive_failures INT UNSIGNED NOT NULL DEFAULT 0,
    notes              TEXT DEFAULT NULL,
    updated_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_endpoint_key (endpoint_key),
    INDEX idx_broken (enabled, consecutive_failures)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- queryIds seen in a real browser, promoted into linkedin_endpoints by a job.
CREATE TABLE IF NOT EXISTS linkedin_endpoint_observations (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    query_name       VARCHAR(128) NOT NULL COMMENT 'e.g. voyagerSearchDashClusters',
    query_id         VARCHAR(191) NOT NULL COMMENT 'Full name.hash',
    device_id        INT UNSIGNED DEFAULT NULL,
    tenant_id        INT UNSIGNED DEFAULT NULL,
    client_version   VARCHAR(32) DEFAULT NULL,
    sample_variables TEXT DEFAULT NULL,
    promoted_at      DATETIME DEFAULT NULL,
    observed_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_query_device (query_id, device_id),
    INDEX idx_name_seen (query_name, observed_at),
    INDEX idx_unpromoted (promoted_at, observed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------
-- Browser relay
-- ----------------------------------------

-- The server asks the extension to perform a Voyager call from the user's own
-- browser and IP. request_json is built server-side from linkedin_endpoints and
-- MUST be validated against the registry before it is handed out -- the server
-- must never be able to ask the extension to fetch an arbitrary URL.
CREATE TABLE IF NOT EXISTS outreach_relay_requests (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id           INT UNSIGNED NOT NULL,
    linkedin_account_id INT UNSIGNED NOT NULL,
    device_id           INT UNSIGNED DEFAULT NULL,
    endpoint_key        VARCHAR(64) DEFAULT NULL,
    request_json        MEDIUMTEXT NOT NULL COMMENT '{method,path,headers,body}',
    status              ENUM('pending','claimed','done','failed','expired') NOT NULL DEFAULT 'pending',
    http_status         SMALLINT UNSIGNED DEFAULT NULL,
    response_json       LONGTEXT DEFAULT NULL,
    error               TEXT DEFAULT NULL,
    claimed_at          DATETIME DEFAULT NULL,
    completed_at        DATETIME DEFAULT NULL,
    expires_at          DATETIME NOT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_account_pending (linkedin_account_id, status, created_at),
    INDEX idx_expiry (status, expires_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (linkedin_account_id) REFERENCES linkedin_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
