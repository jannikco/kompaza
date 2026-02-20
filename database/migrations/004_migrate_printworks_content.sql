-- ============================================
-- MIGRATION 004: Migrate PrintWorks Content
-- Cross-database INSERT...SELECT from printworks.* → kompaza.*
-- Both databases are on the same MySQL server.
--
-- PREREQUISITE: Run 003_add_printworks_tenant.sql first.
-- ============================================

SET NAMES utf8mb4;

-- Get the PrintWorks tenant ID
SET @tenant_id = (SELECT id FROM kompaza.tenants WHERE slug = 'printworks');

-- ----------------------------------------
-- 1. Migrate articles (5 rows)
-- ----------------------------------------
INSERT INTO kompaza.articles (
    tenant_id, slug, title, excerpt, content, featured_image, meta_description,
    category, tags, status, published_at, author_name, view_count,
    created_at, updated_at
)
SELECT
    @tenant_id,
    slug, title, excerpt, content, featured_image, meta_description,
    category, tags, status, published_at, author_name, view_count,
    created_at, updated_at
FROM printworks.articles;

-- ----------------------------------------
-- 2. Migrate ebooks (3 rows)
-- ----------------------------------------
INSERT INTO kompaza.ebooks (
    tenant_id, slug, title, subtitle, description,
    hero_headline, hero_subheadline, cover_image_path, features,
    pdf_filename, pdf_original_name, page_count, price_dkk, meta_description,
    status, view_count, download_count, created_at, updated_at
)
SELECT
    @tenant_id,
    slug, title, subtitle, description,
    hero_headline, hero_subheadline, cover_image_path, features,
    pdf_filename, pdf_original_name, page_count, price_dkk, meta_description,
    status, view_count, download_count, created_at, updated_at
FROM printworks.ebooks;

-- ----------------------------------------
-- 3. Migrate lead_magnets (3 rows)
-- Note: brevo_list_id is INT in PrintWorks, VARCHAR(50) in Kompaza — CAST handles this
-- ----------------------------------------
INSERT INTO kompaza.lead_magnets (
    tenant_id, slug, title, subtitle, meta_description,
    hero_headline, hero_subheadline, hero_cta_text, hero_bg_color, hero_image_path,
    features_headline, features, pdf_filename, pdf_original_name,
    email_subject, email_body_html, brevo_list_id,
    status, view_count, signup_count, created_at, updated_at
)
SELECT
    @tenant_id,
    slug, title, subtitle, meta_description,
    hero_headline, hero_subheadline, hero_cta_text, hero_bg_color, hero_image_path,
    features_headline, features, pdf_filename, pdf_original_name,
    email_subject, email_body_html, CAST(brevo_list_id AS CHAR),
    status, view_count, signup_count, created_at, updated_at
FROM printworks.lead_magnets;

-- ----------------------------------------
-- 4. Migrate email_signups (19 rows)
-- ----------------------------------------
INSERT INTO kompaza.email_signups (
    tenant_id, email, name, source_type, source_id, source_slug,
    brevo_synced, brevo_synced_at, ip_address, user_agent, created_at
)
SELECT
    @tenant_id,
    email, name, source_type, source_id, source_slug,
    brevo_synced, brevo_synced_at, ip_address, user_agent, created_at
FROM printworks.email_signups;

-- ----------------------------------------
-- 5. Migrate settings (14 rows)
-- ----------------------------------------
INSERT INTO kompaza.settings (
    tenant_id, setting_key, setting_value, setting_type, description, updated_at
)
SELECT
    @tenant_id,
    setting_key, setting_value, setting_type, description, updated_at
FROM printworks.settings;
