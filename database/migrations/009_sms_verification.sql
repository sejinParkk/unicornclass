-- SMS 인증 테이블 (알리고 미연동 — DB OTP 방식)
CREATE TABLE IF NOT EXISTS `lc_sms_verification` (
    `idx`        INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `phone`      VARCHAR(20)      NOT NULL COMMENT '010-XXXX-XXXX 형식',
    `code`       VARCHAR(6)       NOT NULL COMMENT '6자리 랜덤 숫자',
    `purpose`    ENUM('find_id','find_password','register') NOT NULL,
    `mb_id`      VARCHAR(50)      DEFAULT NULL COMMENT '비밀번호 찾기 시 아이디 선검증용',
    `is_used`    TINYINT(1)       DEFAULT 0,
    `fail_count` TINYINT UNSIGNED DEFAULT 0 COMMENT '인증 실패 횟수 (5회 초과 시 무효화)',
    `expires_at` DATETIME         NOT NULL,
    `created_at` DATETIME         DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`idx`),
    KEY `idx_phone_purpose` (`phone`, `purpose`),
    KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
