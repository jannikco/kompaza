-- ============================================
-- MIGRATION 006: Course Platform
-- ============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Alter existing tables
ALTER TABLE tenants ADD COLUMN feature_courses BOOLEAN DEFAULT FALSE AFTER feature_connectpilot;
ALTER TABLE plans ADD COLUMN max_courses INT UNSIGNED DEFAULT NULL AFTER max_lead_magnets;
ALTER TABLE order_items
    ADD COLUMN item_type ENUM('product','course') DEFAULT 'product' AFTER order_id,
    ADD COLUMN course_id INT UNSIGNED DEFAULT NULL AFTER product_id;

-- ============================================
-- COURSES
-- ============================================

CREATE TABLE IF NOT EXISTS courses (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    slug VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(500) DEFAULT NULL,
    description LONGTEXT DEFAULT NULL,
    short_description TEXT DEFAULT NULL,

    -- Media
    cover_image_path VARCHAR(500) DEFAULT NULL,
    promo_video_s3_key VARCHAR(500) DEFAULT NULL,

    -- Pricing
    pricing_type ENUM('free','one_time','subscription') DEFAULT 'free',
    price_dkk DECIMAL(10,2) DEFAULT NULL,
    compare_price_dkk DECIMAL(10,2) DEFAULT NULL,
    subscription_price_monthly_dkk DECIMAL(10,2) DEFAULT NULL,
    subscription_price_yearly_dkk DECIMAL(10,2) DEFAULT NULL,
    stripe_monthly_price_id VARCHAR(255) DEFAULT NULL,
    stripe_yearly_price_id VARCHAR(255) DEFAULT NULL,

    -- Settings
    status ENUM('draft','published','archived') DEFAULT 'draft',
    is_featured BOOLEAN DEFAULT FALSE,
    drip_enabled BOOLEAN DEFAULT FALSE,
    drip_interval_days INT UNSIGNED DEFAULT NULL,

    -- Instructor
    instructor_name VARCHAR(255) DEFAULT NULL,
    instructor_bio TEXT DEFAULT NULL,
    instructor_image_path VARCHAR(500) DEFAULT NULL,

    -- Stats
    view_count INT UNSIGNED DEFAULT 0,
    enrollment_count INT UNSIGNED DEFAULT 0,
    total_duration_seconds INT UNSIGNED DEFAULT 0,
    total_lessons INT UNSIGNED DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slug_tenant (slug, tenant_id),
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_featured (tenant_id, is_featured),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- ============================================
-- COURSE MODULES (sections/chapters)
-- ============================================

CREATE TABLE IF NOT EXISTS course_modules (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    course_id INT UNSIGNED NOT NULL,
    tenant_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    sort_order INT UNSIGNED DEFAULT 0,
    drip_days_after_enrollment INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_course_sort (course_id, sort_order),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- ============================================
-- COURSE LESSONS
-- ============================================

CREATE TABLE IF NOT EXISTS course_lessons (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    module_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    tenant_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) DEFAULT NULL,

    -- Content
    lesson_type ENUM('video','text','video_text','download') DEFAULT 'video',
    text_content LONGTEXT DEFAULT NULL,

    -- Video
    video_s3_key VARCHAR(500) DEFAULT NULL,
    video_original_filename VARCHAR(255) DEFAULT NULL,
    video_duration_seconds INT UNSIGNED DEFAULT NULL,
    video_file_size_bytes BIGINT UNSIGNED DEFAULT NULL,
    video_thumbnail_s3_key VARCHAR(500) DEFAULT NULL,
    video_status ENUM('pending','uploading','transcoding','ready','failed') DEFAULT NULL,
    video_error_message TEXT DEFAULT NULL,
    video_variants JSON DEFAULT NULL,

    -- Resources
    resources JSON DEFAULT NULL,

    -- Settings
    is_preview BOOLEAN DEFAULT FALSE,
    sort_order INT UNSIGNED DEFAULT 0,
    drip_days_after_enrollment INT UNSIGNED DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_module_sort (module_id, sort_order),
    INDEX idx_course (course_id),
    FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- ============================================
-- COURSE ENROLLMENTS
-- ============================================

CREATE TABLE IF NOT EXISTS course_enrollments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,

    -- Source
    enrollment_source ENUM('purchase','subscription','manual','free') DEFAULT 'free',
    order_id INT UNSIGNED DEFAULT NULL,
    stripe_subscription_id VARCHAR(255) DEFAULT NULL,

    -- Status
    status ENUM('active','expired','cancelled','refunded') DEFAULT 'active',

    -- Progress cache
    completed_lessons INT UNSIGNED DEFAULT 0,
    total_lessons INT UNSIGNED DEFAULT 0,
    progress_percent DECIMAL(5,2) DEFAULT 0.00,

    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    last_accessed_at TIMESTAMP NULL,

    UNIQUE KEY unique_course_user (course_id, user_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
);

-- ============================================
-- COURSE PROGRESS (per-lesson tracking)
-- ============================================

CREATE TABLE IF NOT EXISTS course_progress (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    enrollment_id INT UNSIGNED NOT NULL,
    lesson_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,

    is_completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    video_position_seconds INT UNSIGNED DEFAULT 0,
    video_watched_percent DECIMAL(5,2) DEFAULT 0.00,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_enrollment_lesson (enrollment_id, lesson_id),
    INDEX idx_user_lesson (user_id, lesson_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (enrollment_id) REFERENCES course_enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- VIDEO TRANSCODE JOBS
-- ============================================

CREATE TABLE IF NOT EXISTS video_transcode_jobs (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    lesson_id INT UNSIGNED NOT NULL,
    source_local_path VARCHAR(500) NOT NULL,
    status ENUM('pending','processing','uploading','completed','failed') DEFAULT 'pending',
    error_message TEXT DEFAULT NULL,
    output_s3_key VARCHAR(500) DEFAULT NULL,
    output_variants JSON DEFAULT NULL,
    duration_seconds INT UNSIGNED DEFAULT NULL,
    thumbnail_s3_key VARCHAR(500) DEFAULT NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_lesson (lesson_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES course_lessons(id) ON DELETE CASCADE
);

SET FOREIGN_KEY_CHECKS = 1;
