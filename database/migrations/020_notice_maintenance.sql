-- 공지사항 점검 플래그 추가
ALTER TABLE lc_notice
    ADD COLUMN is_maintenance TINYINT(1) NOT NULL DEFAULT 0 AFTER is_pinned;
