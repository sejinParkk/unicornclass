-- 024_login_fail_count.sql
-- 로그인 실패 횟수 추적 및 자동 잠금 기능

ALTER TABLE lc_member
  ADD COLUMN `login_fail_count` TINYINT UNSIGNED NOT NULL DEFAULT 0
    COMMENT '연속 로그인 실패 횟수 (5회 도달 시 is_active=0 자동 잠금)'
  AFTER `last_login_at`;
