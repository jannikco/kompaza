ALTER TABLE lead_magnets
    ADD COLUMN chapters JSON DEFAULT NULL AFTER features,
    ADD COLUMN key_statistics JSON DEFAULT NULL AFTER chapters,
    ADD COLUMN before_after JSON DEFAULT NULL AFTER faq,
    ADD COLUMN author_bio TEXT DEFAULT NULL AFTER before_after,
    ADD COLUMN testimonial_templates JSON DEFAULT NULL AFTER author_bio,
    ADD COLUMN social_proof JSON DEFAULT NULL AFTER testimonial_templates;
