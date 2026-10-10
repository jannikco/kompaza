-- ============================================
-- MIGRATION 046: Drop plaintext LinkedIn cookies
--
-- ***  DESTRUCTIVE. RUN BY HAND. NOT LISTED IN apply-launch-migrations.sh.  ***
--
-- Removes linkedin_accounts.li_at_cookie and .csrf_token, which stored LinkedIn
-- session cookies in the clear. Anyone with database read access could take over
-- every connected LinkedIn account.
--
-- PRECONDITIONS -- all three must hold before you run this:
--
--   1. OUTREACH_ENCRYPTION_KEY is set in .env AND backed up somewhere that is NOT the
--      database backup. Losing it means every tenant must re-pair their account.
--
--   2. mysqldump has been taken:
--        mysqldump kompaza linkedin_accounts > ~/backups/linkedin_accounts-$(date +%F).sql
--
--   3. The backfill reports zero remaining rows:
--        php scripts/outreach-encrypt-cookies.php
--        -> "0 rows still unencrypted"
--
-- Verify precondition 3 yourself before proceeding:
--
--     SELECT COUNT(*) FROM linkedin_accounts
--      WHERE encryption_version = 0 AND li_at_cookie IS NOT NULL AND li_at_cookie <> '';
--
--   If that returns anything other than 0, STOP and run the backfill first --
--   dropping these columns would destroy those sessions irrecoverably.
-- ============================================

SET NAMES utf8mb4;

-- Fail loudly rather than silently destroying sessions that were never encrypted.
DROP PROCEDURE IF EXISTS kz_assert_cookies_encrypted;

DELIMITER //
CREATE PROCEDURE kz_assert_cookies_encrypted()
BEGIN
    DECLARE unencrypted INT DEFAULT 0;

    SELECT COUNT(*) INTO unencrypted
      FROM linkedin_accounts
     WHERE encryption_version = 0
       AND li_at_cookie IS NOT NULL
       AND li_at_cookie <> '';

    IF unencrypted > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ABORTED: LinkedIn sessions are still unencrypted. Run scripts/outreach-encrypt-cookies.php first.';
    END IF;
END //
DELIMITER ;

CALL kz_assert_cookies_encrypted();
DROP PROCEDURE kz_assert_cookies_encrypted;

-- Belt and braces: blank anything left behind on already-encrypted rows.
UPDATE linkedin_accounts
   SET li_at_cookie = NULL, csrf_token = NULL
 WHERE encryption_version >= 1;

ALTER TABLE linkedin_accounts DROP COLUMN IF EXISTS li_at_cookie;
ALTER TABLE linkedin_accounts DROP COLUMN IF EXISTS csrf_token;
