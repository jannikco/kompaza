ALTER TABLE lead_magnets
    ADD COLUMN hero_badge VARCHAR(100) DEFAULT NULL AFTER hero_cta_text,
    ADD COLUMN hero_headline_accent VARCHAR(255) DEFAULT NULL AFTER hero_badge;
