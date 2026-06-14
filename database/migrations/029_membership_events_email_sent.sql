-- Migration 029: membership_events email dispatch tracking
--
-- Adds `email_sent` (and a timestamp) to `membership_events` so the membership
-- lifecycle email cron (process-membership-events) can find unsent events and
-- mark each one once. Written defensively (checks information_schema before
-- every change, same pattern as 020a) so it is safe to run on a fresh
-- schema.sql install AND on the existing production database whether or not
-- the columns already exist.

DROP PROCEDURE IF EXISTS kz_add_col_029;

DELIMITER //
-- Add a column only if it does not already exist
CREATE PROCEDURE kz_add_col_029(IN tname VARCHAR(64), IN cname VARCHAR(64), IN cdef TEXT)
BEGIN
    IF (SELECT COUNT(*) FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tname AND COLUMN_NAME = cname) = 0 THEN
        SET @ddl = CONCAT('ALTER TABLE `', tname, '` ADD COLUMN `', cname, '` ', cdef);
        PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;
    END IF;
END //
DELIMITER ;

CALL kz_add_col_029('membership_events', 'email_sent',    "TINYINT(1) NOT NULL DEFAULT 0");
CALL kz_add_col_029('membership_events', 'email_sent_at', "TIMESTAMP NULL");

DROP PROCEDURE IF EXISTS kz_add_col_029;

-- Index so the cron can quickly find events awaiting an email (guarded)
DROP PROCEDURE IF EXISTS kz_add_index_029;
DELIMITER //
CREATE PROCEDURE kz_add_index_029(IN tname VARCHAR(64), IN iname VARCHAR(64), IN cols VARCHAR(255))
BEGIN
    IF (SELECT COUNT(*) FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tname AND INDEX_NAME = iname) = 0 THEN
        SET @ddl = CONCAT('CREATE INDEX `', iname, '` ON `', tname, '` (', cols, ')');
        PREPARE s FROM @ddl; EXECUTE s; DEALLOCATE PREPARE s;
    END IF;
END //
DELIMITER ;
CALL kz_add_index_029('membership_events', 'idx_membership_events_email_sent', 'email_sent');
DROP PROCEDURE IF EXISTS kz_add_index_029;
