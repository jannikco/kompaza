-- Tenant setup checklist / onboarding state (JSON)
ALTER TABLE tenants
    ADD COLUMN IF NOT EXISTS onboarding_json JSON DEFAULT NULL AFTER custom_footer_html;
