-- Membership plans feature
-- Feature flag
ALTER TABLE tenants ADD COLUMN feature_memberships BOOLEAN DEFAULT FALSE;

-- Membership plans (tenant defines their tiers)
CREATE TABLE membership_plans (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    tier_level INT UNSIGNED NOT NULL DEFAULT 0,
    description TEXT DEFAULT NULL,
    price_monthly INT DEFAULT NULL,
    price_yearly INT DEFAULT NULL,
    stripe_monthly_price_id VARCHAR(255) DEFAULT NULL,
    stripe_yearly_price_id VARCHAR(255) DEFAULT NULL,
    max_courses INT DEFAULT NULL,
    max_ebooks INT DEFAULT NULL,
    can_access_prompts BOOLEAN DEFAULT FALSE,
    can_post_community BOOLEAN DEFAULT FALSE,
    can_access_live_qa BOOLEAN DEFAULT FALSE,
    community_read_only BOOLEAN DEFAULT TRUE,
    discount_percent INT DEFAULT 0,
    is_default BOOLEAN DEFAULT FALSE,
    status ENUM('active','archived') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    UNIQUE KEY unique_tenant_slug (tenant_id, slug)
);

-- Customer memberships
CREATE TABLE customer_memberships (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NOT NULL,
    stripe_subscription_id VARCHAR(255) DEFAULT NULL,
    stripe_customer_id VARCHAR(255) DEFAULT NULL,
    billing_interval ENUM('monthly','yearly') DEFAULT 'monthly',
    status ENUM('active','trialing','past_due','cancelled','expired') DEFAULT 'active',
    current_period_start TIMESTAMP NULL,
    current_period_end TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES membership_plans(id),
    UNIQUE KEY unique_tenant_user (tenant_id, user_id),
    INDEX idx_stripe_sub (stripe_subscription_id)
);

-- Pro content selections (which courses/ebooks the member picked)
CREATE TABLE membership_content_selections (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    content_type ENUM('course','ebook') NOT NULL,
    content_id INT UNSIGNED NOT NULL,
    selected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_selection (tenant_id, user_id, content_type, content_id),
    INDEX idx_user (tenant_id, user_id)
);

-- Course/ebook access tier
ALTER TABLE courses ADD COLUMN membership_tier_level INT DEFAULT NULL;
ALTER TABLE ebooks ADD COLUMN membership_tier_level INT DEFAULT NULL;

-- Add 'membership' to course enrollment source
ALTER TABLE course_enrollments MODIFY COLUMN enrollment_source ENUM('purchase','subscription','manual','free','membership') DEFAULT 'free';

-- Membership events log
CREATE TABLE membership_events (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    membership_id INT UNSIGNED DEFAULT NULL,
    event_type VARCHAR(100) NOT NULL,
    stripe_event_id VARCHAR(255) DEFAULT NULL,
    payload JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_stripe_event (stripe_event_id)
);
