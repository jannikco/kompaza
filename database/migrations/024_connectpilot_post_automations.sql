-- ============================================
-- MIGRATION 024: ConnectPilot Post Automations
-- Comment-to-DM automation (LeadShark clone)
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------
-- Post Automations
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS connectpilot_post_automations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    linkedin_account_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    post_url VARCHAR(500) NOT NULL,
    post_urn VARCHAR(255) DEFAULT NULL,
    trigger_keyword VARCHAR(100) NOT NULL,
    auto_reply_enabled TINYINT(1) DEFAULT 1,
    auto_reply_template TEXT,
    auto_dm_enabled TINYINT(1) DEFAULT 1,
    dm_template TEXT,
    lead_magnet_id INT UNSIGNED DEFAULT NULL,
    status ENUM('active','paused','completed') DEFAULT 'active',
    comments_detected INT DEFAULT 0,
    keyword_matches INT DEFAULT 0,
    replies_sent INT DEFAULT 0,
    dms_sent INT DEFAULT 0,
    leads_captured INT DEFAULT 0,
    last_checked_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    FOREIGN KEY (linkedin_account_id) REFERENCES linkedin_accounts(id),
    FOREIGN KEY (lead_magnet_id) REFERENCES lead_magnets(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------
-- Post Comments (tracking)
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS connectpilot_post_comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    automation_id INT UNSIGNED NOT NULL,
    tenant_id INT UNSIGNED NOT NULL,
    comment_urn VARCHAR(255) NOT NULL,
    commenter_profile_url VARCHAR(500),
    commenter_urn VARCHAR(255),
    commenter_name VARCHAR(255),
    commenter_headline VARCHAR(500),
    comment_text TEXT,
    keyword_matched TINYINT(1) DEFAULT 0,
    reply_sent TINYINT(1) DEFAULT 0,
    reply_sent_at DATETIME DEFAULT NULL,
    dm_sent TINYINT(1) DEFAULT 0,
    dm_sent_at DATETIME DEFAULT NULL,
    lead_id INT UNSIGNED DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_comment_urn (comment_urn),
    INDEX idx_automation (automation_id),
    INDEX idx_pending (automation_id, keyword_matched, reply_sent, dm_sent),
    FOREIGN KEY (automation_id) REFERENCES connectpilot_post_automations(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
