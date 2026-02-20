-- ============================================
-- MIGRATION 003: Add PrintWorks as First Tenant
-- Creates unlimited plan, tenant, admin user, and custom domain
--
-- BEFORE RUNNING: Generate password hash on server:
--   php -r "echo password_hash('changeme123', PASSWORD_DEFAULT);"
-- Then replace REPLACE_WITH_HASH below with the output.
-- ============================================

SET NAMES utf8mb4;

-- ----------------------------------------
-- 1. Create hidden "Unlimited" plan
-- ----------------------------------------
INSERT INTO plans (name, slug, price_monthly_usd, price_yearly_usd, max_customers, max_leads, max_campaigns, max_products, max_lead_magnets, features_json, is_active, sort_order)
VALUES (
    'Unlimited',
    'unlimited',
    0.00,
    0.00,
    NULL,  -- unlimited
    NULL,  -- unlimited
    NULL,  -- unlimited
    NULL,  -- unlimited
    NULL,  -- unlimited
    NULL,
    FALSE, -- hidden from pricing page
    -1     -- sorted before all visible plans
);

SET @plan_id = LAST_INSERT_ID();

-- ----------------------------------------
-- 2. Create PrintWorks tenant
-- ----------------------------------------
INSERT INTO tenants (
    uuid, name, slug, status,
    primary_color, secondary_color,
    company_name, tagline, email,
    currency, tax_rate,
    feature_blog, feature_ebooks, feature_lead_magnets, feature_orders, feature_connectpilot,
    plan_id, trial_ends_at, subscription_status
) VALUES (
    UUID(), 'PrintWorks', 'printworks', 'active',
    '#1e3a5f', '#6366f1',
    'PrintWorks', 'Professionelle digitale assets til din LinkedIn-strategi', 'info@printworks.dk',
    'DKK', 25.00,
    TRUE, TRUE, TRUE, FALSE, FALSE,
    @plan_id, NULL, 'active'
);

SET @tenant_id = LAST_INSERT_ID();

-- ----------------------------------------
-- 3. Create tenant admin user
-- ----------------------------------------
-- IMPORTANT: Replace REPLACE_WITH_HASH with actual bcrypt hash from:
--   php -r "echo password_hash('changeme123', PASSWORD_DEFAULT);"
INSERT INTO users (tenant_id, role, name, email, password_hash, status)
VALUES (
    @tenant_id,
    'tenant_admin',
    'PrintWorks Admin',
    'info@printworks.dk',
    'REPLACE_WITH_HASH',
    'active'
);

SET @user_id = LAST_INSERT_ID();

-- ----------------------------------------
-- 4. Set owner on tenant
-- ----------------------------------------
UPDATE tenants SET owner_user_id = @user_id WHERE id = @tenant_id;

-- ----------------------------------------
-- 5. Register custom domain
-- ----------------------------------------
INSERT INTO tenant_domains (tenant_id, domain, ssl_status)
VALUES (@tenant_id, 'printworks.dk', 'active');
