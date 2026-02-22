-- Email verification tokens table
CREATE TABLE IF NOT EXISTS email_verification_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add email_verified_at column to users table
-- Note: IF NOT EXISTS is MariaDB-only. For MySQL 8.0, check column existence first.
-- Safe to re-run: will error harmlessly if column already exists.
ALTER TABLE users ADD COLUMN email_verified_at DATETIME NULL DEFAULT NULL AFTER status;

-- Mark all existing users as verified (they registered before this feature)
UPDATE users SET email_verified_at = created_at WHERE email_verified_at IS NULL;
