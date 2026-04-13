-- 강의 자료 테이블 (파일 업로드 + 외부 링크)
CREATE TABLE IF NOT EXISTS lc_class_file (
    file_idx     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    class_idx    INT UNSIGNED NOT NULL,
    file_type    ENUM('file','link') NOT NULL DEFAULT 'file',
    title        VARCHAR(200) NOT NULL,
    file_path    VARCHAR(500) NULL,
    file_size    INT UNSIGNED NULL DEFAULT 0,
    external_url VARCHAR(500) NULL,
    sort_order   SMALLINT NOT NULL DEFAULT 0,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (file_idx),
    KEY idx_class_idx (class_idx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
