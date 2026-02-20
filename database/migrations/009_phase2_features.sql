-- ============================================
-- PHASE 2: Newsletter, Email Sequences, Invoices,
-- Discount Codes, Company Licenses, Consultations,
-- Mastermind Programs
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;

-- 2.1 Newsletters
CREATE TABLE IF NOT EXISTS newsletters (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    subject VARCHAR(500) NOT NULL,
    body_html LONGTEXT DEFAULT NULL,
    status ENUM('draft','sending','sent','failed') DEFAULT 'draft',
    sent_at TIMESTAMP NULL DEFAULT NULL,
    recipient_count INT UNSIGNED DEFAULT 0,
    failed_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- 2.2 Email Sequences
CREATE TABLE IF NOT EXISTS email_sequences (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    trigger_type ENUM('quiz_completion','lead_magnet_signup','purchase','course_enrollment','manual') NOT NULL DEFAULT 'manual',
    trigger_id INT UNSIGNED DEFAULT NULL,
    status ENUM('draft','active','paused') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_trigger (trigger_type, trigger_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS email_sequence_steps (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    sequence_id INT UNSIGNED NOT NULL,
    day_number INT UNSIGNED NOT NULL DEFAULT 1,
    subject VARCHAR(500) NOT NULL,
    body_html TEXT DEFAULT NULL,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sequence_sort (sequence_id, sort_order),
    FOREIGN KEY (sequence_id) REFERENCES email_sequences(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS email_sequence_enrollments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    sequence_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(255) DEFAULT NULL,
    status ENUM('active','completed','cancelled') DEFAULT 'active',
    current_step INT UNSIGNED DEFAULT 0,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_sequence_status (sequence_id, status),
    INDEX idx_email (email),
    FOREIGN KEY (sequence_id) REFERENCES email_sequences(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS email_sequence_logs (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    enrollment_id INT UNSIGNED NOT NULL,
    step_id INT UNSIGNED NOT NULL,
    status ENUM('sent','failed') DEFAULT 'sent',
    error_message TEXT DEFAULT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_enrollment (enrollment_id),
    FOREIGN KEY (enrollment_id) REFERENCES email_sequence_enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (step_id) REFERENCES email_sequence_steps(id) ON DELETE CASCADE
);

-- 2.4 Discount Codes
CREATE TABLE IF NOT EXISTS discount_codes (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    code VARCHAR(50) NOT NULL,
    type ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage',
    value DECIMAL(10,2) NOT NULL,
    min_order_dkk DECIMAL(10,2) DEFAULT NULL,
    max_uses INT UNSIGNED DEFAULT NULL,
    used_count INT UNSIGNED DEFAULT 0,
    applies_to ENUM('all','courses','products','ebooks') DEFAULT 'all',
    expires_at TIMESTAMP NULL DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_code_tenant (code, tenant_id),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS discount_code_uses (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    discount_code_id INT UNSIGNED NOT NULL,
    order_id INT UNSIGNED DEFAULT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    amount_dkk DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (discount_code_id) REFERENCES discount_codes(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 2.5 Company / Team License Management
CREATE TABLE IF NOT EXISTS company_accounts (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    admin_user_id INT UNSIGNED NOT NULL,
    total_licenses INT UNSIGNED DEFAULT 0,
    status ENUM('active','suspended','cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS team_members (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_account_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','invited','active','deactivated') DEFAULT 'pending',
    invited_at TIMESTAMP NULL DEFAULT NULL,
    joined_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_company (company_account_id),
    INDEX idx_user (user_id),
    INDEX idx_email (email),
    FOREIGN KEY (company_account_id) REFERENCES company_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS company_course_licenses (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_account_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    seats_total INT UNSIGNED NOT NULL DEFAULT 1,
    seats_used INT UNSIGNED DEFAULT 0,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_company_course (company_account_id, course_id),
    FOREIGN KEY (company_account_id) REFERENCES company_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- 2.6 Consultation Booking System
CREATE TABLE IF NOT EXISTS consultation_types (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    duration_minutes INT UNSIGNED NOT NULL DEFAULT 60,
    price_dkk DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('active','inactive') DEFAULT 'active',
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS consultation_bookings (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    type_id INT UNSIGNED DEFAULT NULL,
    booking_number VARCHAR(50) NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) DEFAULT NULL,
    company VARCHAR(255) DEFAULT NULL,
    project_description TEXT DEFAULT NULL,
    preferred_date DATE DEFAULT NULL,
    preferred_time VARCHAR(20) DEFAULT NULL,
    urgency ENUM('low','medium','high') DEFAULT 'medium',
    status ENUM('pending','confirmed','completed','cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid','paid','refunded') DEFAULT 'unpaid',
    stripe_payment_intent_id VARCHAR(255) DEFAULT NULL,
    admin_notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_booking_tenant (booking_number, tenant_id),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (type_id) REFERENCES consultation_types(id) ON DELETE SET NULL
);

-- 2.7 Mastermind / Cohort Programs
CREATE TABLE IF NOT EXISTS mastermind_programs (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description LONGTEXT DEFAULT NULL,
    short_description TEXT DEFAULT NULL,
    cover_image_path VARCHAR(500) DEFAULT NULL,
    status ENUM('draft','published','archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slug_tenant (slug, tenant_id),
    INDEX idx_tenant_status (tenant_id, status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS mastermind_tiers (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    program_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    upfront_price_dkk DECIMAL(10,2) DEFAULT 0.00,
    monthly_price_dkk DECIMAL(10,2) DEFAULT 0.00,
    max_members INT UNSIGNED DEFAULT NULL,
    stripe_price_id VARCHAR(255) DEFAULT NULL,
    features JSON DEFAULT NULL,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_program (program_id),
    FOREIGN KEY (program_id) REFERENCES mastermind_programs(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS mastermind_enrollments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    program_id INT UNSIGNED NOT NULL,
    tier_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    enrollment_number VARCHAR(50) NOT NULL,
    status ENUM('active','paused','cancelled','completed') DEFAULT 'active',
    stripe_customer_id VARCHAR(255) DEFAULT NULL,
    stripe_subscription_id VARCHAR(255) DEFAULT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cancelled_at TIMESTAMP NULL DEFAULT NULL,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY unique_enrollment (enrollment_number),
    INDEX idx_tenant (tenant_id),
    INDEX idx_program_user (program_id, user_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES mastermind_programs(id) ON DELETE CASCADE,
    FOREIGN KEY (tier_id) REFERENCES mastermind_tiers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS mastermind_milestones (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    enrollment_id INT UNSIGNED NOT NULL,
    milestone_type VARCHAR(100) NOT NULL,
    notes TEXT DEFAULT NULL,
    achieved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_enrollment (enrollment_id),
    FOREIGN KEY (enrollment_id) REFERENCES mastermind_enrollments(id) ON DELETE CASCADE
);

-- Add invoice fields to orders table
ALTER TABLE orders
    ADD COLUMN invoice_number VARCHAR(50) DEFAULT NULL AFTER tracking_url,
    ADD COLUMN invoice_due_date DATE DEFAULT NULL AFTER invoice_number,
    ADD COLUMN invoice_pdf_path VARCHAR(500) DEFAULT NULL AFTER invoice_due_date,
    ADD COLUMN discount_code_id INT UNSIGNED DEFAULT NULL AFTER discount_dkk;

-- Add feature flags for new features
ALTER TABLE tenants
    ADD COLUMN feature_courses BOOLEAN DEFAULT TRUE AFTER feature_orders,
    ADD COLUMN feature_newsletters BOOLEAN DEFAULT TRUE AFTER feature_courses,
    ADD COLUMN feature_consultations BOOLEAN DEFAULT FALSE AFTER feature_newsletters,
    ADD COLUMN feature_mastermind BOOLEAN DEFAULT FALSE AFTER feature_consultations;

SET FOREIGN_KEY_CHECKS = 1;
