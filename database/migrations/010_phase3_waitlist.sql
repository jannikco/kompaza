-- ============================================
-- PHASE 3: Waitlist System
-- ============================================

-- Add 'waitlist' to email_signups source_type ENUM
ALTER TABLE email_signups
    MODIFY COLUMN source_type ENUM('lead_magnet','ebook','newsletter','article','waitlist') NOT NULL;
