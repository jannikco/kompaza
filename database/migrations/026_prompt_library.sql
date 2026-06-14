-- Prompt library feature
ALTER TABLE tenants ADD COLUMN feature_prompts BOOLEAN DEFAULT FALSE;

CREATE TABLE prompt_categories (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    icon VARCHAR(50) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_slug (tenant_id, slug)
);

CREATE TABLE prompts (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    prompt_text LONGTEXT NOT NULL,
    description TEXT DEFAULT NULL,
    use_case TEXT DEFAULT NULL,
    ai_tool VARCHAR(100) DEFAULT NULL,
    tags JSON DEFAULT NULL,
    membership_tier_level INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    status ENUM('draft','published') DEFAULT 'published',
    copy_count INT UNSIGNED DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES prompt_categories(id) ON DELETE CASCADE,
    INDEX idx_tenant_category (tenant_id, category_id),
    FULLTEXT INDEX ft_search (title, prompt_text, description)
);
