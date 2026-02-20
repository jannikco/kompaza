-- Migration: Create aibootcamphq tenant for AI BootCamp HQ
-- Run on app1 where both kompaza and aibootcamp databases exist

SET @plan_id = (SELECT id FROM plans WHERE slug = 'enterprise' LIMIT 1);

INSERT INTO tenants (
    uuid, name, slug, custom_domain, status,
    primary_color, secondary_color,
    company_name, tagline, email, currency, tax_rate,
    feature_blog, feature_ebooks, feature_lead_magnets, feature_orders,
    feature_connectpilot, feature_courses, feature_newsletters,
    feature_consultations, feature_mastermind, feature_custom_pages,
    plan_id, subscription_status
) VALUES (
    UUID(), 'AI BootCamp HQ', 'aibootcamphq', 'aibootcamphq.com', 'active',
    '#3b82f6', '#764ba2',
    'AI BootCamp HQ', 'Online AI-kurser & certificering', 'info@aibootcamp.dk',
    'DKK', 25.00,
    TRUE, TRUE, TRUE, TRUE,
    FALSE, TRUE, TRUE,
    TRUE, TRUE, TRUE,
    @plan_id, 'active'
);

SET @tid = LAST_INSERT_ID();

-- Custom domain mapping
INSERT INTO tenant_domains (tenant_id, domain, ssl_status, verified_at)
VALUES (@tid, 'aibootcamphq.com', 'active', NOW());

-- Tenant admin user (reuse aibootcamp admin password hash from their DB)
INSERT INTO users (tenant_id, role, name, email, password_hash, status)
VALUES (@tid, 'tenant_admin', 'Jannik Hansen', 'info@aibootcamp.dk',
    (SELECT password_hash FROM aibootcamp.users WHERE role = 'admin' LIMIT 1),
    'active');
