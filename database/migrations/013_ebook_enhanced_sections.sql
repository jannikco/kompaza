ALTER TABLE ebooks
    ADD COLUMN hero_bg_color VARCHAR(7) DEFAULT NULL AFTER hero_subheadline,
    ADD COLUMN hero_cta_text VARCHAR(100) DEFAULT NULL AFTER hero_bg_color,
    ADD COLUMN features_headline VARCHAR(255) DEFAULT NULL AFTER hero_cta_text,
    ADD COLUMN key_metrics JSON DEFAULT NULL AFTER features_headline,
    ADD COLUMN chapters JSON DEFAULT NULL AFTER features,
    ADD COLUMN target_audience JSON DEFAULT NULL AFTER chapters,
    ADD COLUMN testimonials JSON DEFAULT NULL AFTER target_audience,
    ADD COLUMN faq JSON DEFAULT NULL AFTER testimonials;
