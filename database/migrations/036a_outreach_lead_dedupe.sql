-- ============================================
-- MIGRATION 036a: Deduplicate linkedin_leads, then constrain it
--
-- *** THIS DELETES ROWS. mysqldump linkedin_leads FIRST. ***
--
-- Split out of the main sequence (following the 020a precedent) precisely because
-- it is destructive: it can be read, run and verified on its own.
--
-- Why it is needed:
--   cron/connectpilot-post-runner.php INSERTs a lead unconditionally after every
--   successful DM, with no lookup. The same person commenting on two posts is two
--   lead rows. That breaks three things at once:
--     1. per-person enrichment caching -- we would pay twice for one human
--     2. routing -- two rows can hold two different segments for one person
--     3. "have we already messaged them" -- answered per row, not per person
--
-- Keeper selection, in order: a converted lead wins, then one with an email, then
-- the oldest. Anything the losers know that the keeper does not is coalesced across
-- before they are removed, so no captured fact is lost.
--
-- Re-runnable: after the first pass there are no duplicates and the UNIQUE index
-- already exists, so it is a no-op.
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- GROUP_CONCAT is used to pick the keeper; the default 1024-byte cap would silently
-- truncate the id list for a person with many duplicate rows and pick the wrong one.
SET SESSION group_concat_max_len = 1048576;

-- ----------------------------------------
-- 1. Choose one keeper per (tenant, person)
-- ----------------------------------------
DROP TEMPORARY TABLE IF EXISTS _outreach_lead_keepers;
CREATE TEMPORARY TABLE _outreach_lead_keepers (
    tenant_id  INT UNSIGNED NOT NULL,
    member_urn VARCHAR(191) NOT NULL,
    keep_id    INT UNSIGNED NOT NULL,
    PRIMARY KEY (tenant_id, member_urn),
    INDEX idx_keep (keep_id)
) ENGINE=InnoDB;

INSERT INTO _outreach_lead_keepers (tenant_id, member_urn, keep_id)
SELECT tenant_id,
       member_urn,
       CAST(SUBSTRING_INDEX(
           GROUP_CONCAT(id ORDER BY (customer_id IS NOT NULL) DESC,
                                    (email IS NOT NULL)       DESC,
                                    id ASC),
           ',', 1) AS UNSIGNED)
  FROM linkedin_leads
 WHERE member_urn IS NOT NULL AND member_urn <> ''
 GROUP BY tenant_id, member_urn
HAVING COUNT(*) > 1;

-- ----------------------------------------
-- 2. Coalesce what the losers know onto the keeper
-- ----------------------------------------
-- Aggregate the losers FIRST, then apply once.
--
-- The obvious form -- UPDATE keeper JOIN loser SET keeper.x = COALESCE(keeper.x,
-- loser.x) -- is wrong whenever a person has more than two rows: a multi-table UPDATE
-- applies a single matching row per target, so only ONE loser's values land and the
-- rest are silently discarded. That is tolerable for a headline; it is not tolerable
-- for do_not_engage, where dropping the flag means messaging someone who opted out.
--
-- MAX() ignores NULLs, which is exactly the "take any non-null loser value" semantic
-- we want, and it is deterministic regardless of how many duplicates exist.
DROP TEMPORARY TABLE IF EXISTS _outreach_lead_merge;
CREATE TEMPORARY TABLE _outreach_lead_merge (
    keep_id INT UNSIGNED NOT NULL PRIMARY KEY,
    -- Names matter here: the keeper is chosen for having an email, which is often the
    -- row created from a link click rather than from a comment, so it is exactly the
    -- row most likely to be missing a name the duplicates hold.
    first_name VARCHAR(255) NULL,
    last_name VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(50) NULL,
    company VARCHAR(255) NULL,
    job_title VARCHAR(255) NULL,
    headline VARCHAR(500) NULL,
    location VARCHAR(255) NULL,
    industry VARCHAR(255) NULL,
    notes TEXT NULL,
    linkedin_username VARCHAR(191) NULL,
    profile_image_url VARCHAR(500) NULL,
    linkedin_profile_url VARCHAR(500) NULL,
    -- DATETIME, not TIMESTAMP: MAX() over an all-NULL group trips TIMESTAMP's
    -- zero-date handling on the way into the temp table.
    last_contacted_at DATETIME NULL,
    last_replied_at DATETIME NULL,
    do_not_engage TINYINT(1) NOT NULL DEFAULT 0,
    archived_at DATETIME NULL
) ENGINE=InnoDB;

INSERT INTO _outreach_lead_merge
SELECT d.keep_id,
       MAX(l.first_name), MAX(l.last_name),
       MAX(l.email), MAX(l.phone), MAX(l.company), MAX(l.job_title), MAX(l.headline),
       MAX(l.location), MAX(l.industry), MAX(l.notes), MAX(l.linkedin_username),
       MAX(l.profile_image_url), MAX(NULLIF(l.linkedin_profile_url, '')),
       -- CAST is required, not cosmetic: MAX() over a TIMESTAMP column yields an
       -- aggregate whose all-NULL case converts to the zero date on the way into a
       -- DATETIME column, which STRICT_TRANS_TABLES + NO_ZERO_DATE then rejects.
       MAX(CAST(l.last_contacted_at AS DATETIME)), MAX(CAST(l.last_replied_at AS DATETIME)),
       MAX(l.do_not_engage), MAX(CAST(l.archived_at AS DATETIME))
  FROM _outreach_lead_keepers d
  JOIN linkedin_leads l
    ON l.tenant_id  = d.tenant_id
   AND l.member_urn = d.member_urn
   AND l.id <> d.keep_id
 GROUP BY d.keep_id;

UPDATE linkedin_leads k
  JOIN _outreach_lead_merge m ON m.keep_id = k.id
   SET k.first_name           = COALESCE(k.first_name, m.first_name),
       k.last_name            = COALESCE(k.last_name, m.last_name),
       k.email                = COALESCE(k.email, m.email),
       k.phone                = COALESCE(k.phone, m.phone),
       k.company              = COALESCE(k.company, m.company),
       k.job_title            = COALESCE(k.job_title, m.job_title),
       k.headline             = COALESCE(k.headline, m.headline),
       k.location             = COALESCE(k.location, m.location),
       k.industry             = COALESCE(k.industry, m.industry),
       k.notes                = COALESCE(k.notes, m.notes),
       k.linkedin_username    = COALESCE(k.linkedin_username, m.linkedin_username),
       k.profile_image_url    = COALESCE(k.profile_image_url, m.profile_image_url),
       k.linkedin_profile_url = COALESCE(NULLIF(k.linkedin_profile_url, ''), m.linkedin_profile_url),
       -- Most recent contact wins. No epoch sentinel: these are TIMESTAMP columns
       -- whose valid range begins at 1970-01-01 00:00:01, so COALESCE(…, '1970-01-01')
       -- is out of range and aborts the statement under strict mode.
       k.last_contacted_at    = CASE
                                    WHEN k.last_contacted_at IS NULL THEN m.last_contacted_at
                                    WHEN m.last_contacted_at IS NULL THEN k.last_contacted_at
                                    ELSE GREATEST(k.last_contacted_at, m.last_contacted_at)
                                END,
       k.last_replied_at      = CASE
                                    WHEN k.last_replied_at IS NULL THEN m.last_replied_at
                                    WHEN m.last_replied_at IS NULL THEN k.last_replied_at
                                    ELSE GREATEST(k.last_replied_at, m.last_replied_at)
                                END,
       -- Suppression is sticky: if ANY duplicate said do-not-engage, the keeper does.
       k.do_not_engage        = GREATEST(k.do_not_engage, m.do_not_engage),
       k.archived_at          = COALESCE(k.archived_at, m.archived_at);

DROP TEMPORARY TABLE IF EXISTS _outreach_lead_merge;

-- ----------------------------------------
-- 3. Repoint anything referencing a loser
-- ----------------------------------------
-- connectpilot_post_comments.lead_id has no FK (migration 024), so it would silently
-- dangle if we did not repoint it by hand.
UPDATE connectpilot_post_comments c
  JOIN linkedin_leads l ON l.id = c.lead_id
  JOIN _outreach_lead_keepers d
    ON d.tenant_id = l.tenant_id AND d.member_urn = l.member_urn
   SET c.lead_id = d.keep_id
 WHERE c.lead_id <> d.keep_id;

UPDATE connectpilot_activity_log a
  JOIN linkedin_leads l ON l.id = a.lead_id
  JOIN _outreach_lead_keepers d
    ON d.tenant_id = l.tenant_id AND d.member_urn = l.member_urn
   SET a.lead_id = d.keep_id
 WHERE a.lead_id <> d.keep_id;

-- ----------------------------------------
-- 4. Remove the losers
-- ----------------------------------------
DELETE l FROM linkedin_leads l
  JOIN _outreach_lead_keepers d
    ON d.tenant_id = l.tenant_id AND d.member_urn = l.member_urn
 WHERE l.id <> d.keep_id;

DROP TEMPORARY TABLE IF EXISTS _outreach_lead_keepers;

-- ----------------------------------------
-- 5. Make it stick
-- ----------------------------------------
-- MariaDB permits unlimited NULLs in a UNIQUE index, so leads with no member_urn
-- (manual imports, older rows) are unaffected. This is what lets
-- LinkedInLead::upsertByUrn() be a single atomic statement instead of a racy
-- SELECT-then-INSERT.
ALTER TABLE linkedin_leads
    ADD UNIQUE INDEX IF NOT EXISTS uq_tenant_member_urn (tenant_id, member_urn);

SET FOREIGN_KEY_CHECKS = 1;
