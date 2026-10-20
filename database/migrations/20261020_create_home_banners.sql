CREATE TABLE IF NOT EXISTS home_banners (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(160) NULL,
    alt_text VARCHAR(255) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    target_url VARCHAR(500) NULL,
    starts_at DATETIME NULL,
    ends_at DATETIME NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    order_position INT NOT NULL DEFAULT 0,
    created_by INT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_home_banners_visibility (is_active, starts_at, ends_at, order_position),
    INDEX idx_home_banners_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
