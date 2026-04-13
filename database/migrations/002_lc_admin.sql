CREATE TABLE IF NOT EXISTS `lc_admin` (
    `admin_idx`     INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `admin_id`      VARCHAR(50)   NOT NULL,
    `admin_name`    VARCHAR(50)   NOT NULL,
    `admin_password` VARCHAR(255) NOT NULL,
    `is_active`     TINYINT(1)    NOT NULL DEFAULT 1,
    `last_login_at` DATETIME      NULL,
    `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`admin_idx`),
    UNIQUE KEY `uq_admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
