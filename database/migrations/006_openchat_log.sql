-- Migration 006: 오픈채팅 클릭 로그 테이블
-- 실행일: 2026-04-09

CREATE TABLE IF NOT EXISTS lc_openchat_log (
  log_idx    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  class_idx  INT UNSIGNED NOT NULL                 COMMENT '강의 FK',
  member_idx INT UNSIGNED NULL                     COMMENT '비로그인 NULL 허용',
  clicked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '클릭 일시',
  PRIMARY KEY (log_idx),
  KEY idx_class (class_idx),
  KEY idx_date  (clicked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='오픈채팅 클릭 로그';
