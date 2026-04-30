-- 후기 이미지 테이블
CREATE TABLE lc_review_image (
    review_image_idx INT UNSIGNED NOT NULL AUTO_INCREMENT,
    review_idx       INT UNSIGNED NOT NULL,
    image_path       VARCHAR(255) NOT NULL,
    sort_order       TINYINT UNSIGNED DEFAULT 0,
    created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (review_image_idx),
    KEY idx_review_idx (review_idx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
