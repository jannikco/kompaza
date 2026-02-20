-- Add cover image, target audience, and FAQ columns to lead_magnets
ALTER TABLE lead_magnets
    ADD COLUMN cover_image_path VARCHAR(500) DEFAULT NULL AFTER hero_image_path,
    ADD COLUMN target_audience JSON DEFAULT NULL AFTER features,
    ADD COLUMN faq JSON DEFAULT NULL AFTER target_audience;
