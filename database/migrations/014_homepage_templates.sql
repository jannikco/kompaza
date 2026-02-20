-- Homepage Template System
-- Adds homepage_template picker, hero_image_path, and hero_subtitle to tenants table

ALTER TABLE tenants
  ADD COLUMN homepage_template VARCHAR(50) DEFAULT 'starter' AFTER custom_css;

ALTER TABLE tenants
  ADD COLUMN hero_image_path VARCHAR(500) DEFAULT NULL AFTER homepage_template;

-- hero_subtitle may already exist on some environments; ignore error if so
ALTER TABLE tenants
  ADD COLUMN hero_subtitle VARCHAR(500) DEFAULT NULL AFTER tagline;
