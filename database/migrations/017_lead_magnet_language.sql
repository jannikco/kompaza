-- Add language and section_headings to lead_magnets
ALTER TABLE lead_magnets
    ADD COLUMN language VARCHAR(5) DEFAULT NULL AFTER social_proof,
    ADD COLUMN section_headings JSON DEFAULT NULL AFTER language;
