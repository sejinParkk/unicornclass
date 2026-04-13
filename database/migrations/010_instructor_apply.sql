-- ============================================================
-- 010_instructor_apply.sql
-- 강사 지원 테이블 생성
-- ============================================================

CREATE TABLE IF NOT EXISTS `lc_instructor_apply` (
  `apply_idx`      INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `name`           VARCHAR(50)      NOT NULL COMMENT '이름',
  `phone`          VARCHAR(20)      NOT NULL COMMENT '연락처',
  `email`          VARCHAR(100)     NOT NULL COMMENT '이메일',
  `teach_field`    VARCHAR(50)      NOT NULL DEFAULT '' COMMENT '강의 분야',
  `teach_exp`      VARCHAR(50)      NOT NULL DEFAULT '' COMMENT '강의 경력',
  `bio`            TEXT             NOT NULL COMMENT '자기소개',
  `curriculum`     TEXT             NOT NULL COMMENT '강의 계획',
  `teach_format`   ENUM('free_webinar','paid_vod','mixed') NOT NULL DEFAULT 'free_webinar' COMMENT '선호 강의 형태',
  `sns_instagram`  VARCHAR(255)     NULL,
  `sns_youtube`    VARCHAR(255)     NULL,
  `sns_blog`       VARCHAR(255)     NULL,
  `sns_other`      VARCHAR(255)     NULL,
  `portfolio_link` VARCHAR(500)     NULL COMMENT '포트폴리오 외부 링크',
  `status`         ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reject_reason`  TEXT             NULL,
  `created_at`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`apply_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `lc_instructor_apply_file` (
  `file_idx`       INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `apply_idx`      INT UNSIGNED     NOT NULL COMMENT '강사 지원 FK',
  `file_path`      VARCHAR(255)     NOT NULL COMMENT '저장 파일명 (UUID)',
  `original_name`  VARCHAR(255)     NOT NULL COMMENT '원본 파일명',
  `file_size`      INT UNSIGNED     NOT NULL DEFAULT 0 COMMENT '파일 크기 (bytes)',
  `created_at`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`file_idx`),
  KEY `idx_apply_idx` (`apply_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
