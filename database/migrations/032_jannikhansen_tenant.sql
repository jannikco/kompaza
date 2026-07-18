-- Provision first-customer tenant: jannikhansen.com brand on Kompaza
-- Idempotent: skips insert if slug already exists.

SET @plan_id = (SELECT id FROM plans WHERE slug IN ('enterprise', 'growth', 'starter') ORDER BY FIELD(slug, 'enterprise', 'growth', 'starter') LIMIT 1);

INSERT INTO tenants (
    uuid, name, slug, status,
    primary_color, secondary_color,
    company_name, tagline, email, currency, tax_rate,
    feature_blog, feature_ebooks, feature_lead_magnets, feature_orders,
    feature_connectpilot, feature_courses, feature_newsletters,
    feature_consultations, feature_mastermind, feature_custom_pages,
    feature_memberships, feature_prompts, feature_community,
    plan_id, subscription_status, trial_ends_at,
    custom_css
)
SELECT
    UUID(),
    'Jannik Hansen',
    'jannikhansen',
    'active',
    '#FF5A1F',
    '#0E0E10',
    'Jannik Hansen',
    'One person + AI = an entire team',
    'info@jannikhansen.com',
    'DKK',
    25.00,
    TRUE, TRUE, TRUE, TRUE,
    FALSE, TRUE, TRUE,
    FALSE, FALSE, TRUE,
    FALSE, FALSE, FALSE,
    @plan_id,
    'active',
    NULL,
    '/* JH brand accents on shop chrome */\n:root { --jh-orange: #FF5A1F; }\n.btn-brand { background: linear-gradient(180deg,#FF7A3D,#F0440A) !important; }\n'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tenants WHERE slug = 'jannikhansen');

SET @tid = (SELECT id FROM tenants WHERE slug = 'jannikhansen' LIMIT 1);

-- Ensure feature flags are on even if tenant already existed
UPDATE tenants SET
    feature_custom_pages = 1,
    feature_courses = 1,
    feature_ebooks = 1,
    feature_orders = 1,
    feature_lead_magnets = 1,
    feature_newsletters = 1,
    feature_blog = 1,
    primary_color = COALESCE(NULLIF(primary_color, ''), '#FF5A1F'),
    secondary_color = COALESCE(NULLIF(secondary_color, ''), '#0E0E10'),
    company_name = COALESCE(NULLIF(company_name, ''), 'Jannik Hansen'),
    status = 'active',
    subscription_status = 'active'
WHERE id = @tid;

-- Tenant admin (password: ChangeMe-JH-2026! — rotate immediately)
INSERT INTO users (tenant_id, role, name, email, password_hash, status, email_verified_at)
SELECT @tid, 'tenant_admin', 'Jannik Hansen', 'admin@jannikhansen.kompaza.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'active', NOW()
FROM DUAL
WHERE @tid IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM users WHERE tenant_id = @tid AND email = 'admin@jannikhansen.kompaza.com'
  );

UPDATE tenants SET owner_user_id = (
    SELECT id FROM users WHERE tenant_id = @tid AND role = 'tenant_admin' ORDER BY id ASC LIMIT 1
) WHERE id = @tid AND owner_user_id IS NULL;

SELECT @tid AS jannikhansen_tenant_id;
