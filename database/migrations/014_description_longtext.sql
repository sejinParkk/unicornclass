-- Migration 014: lc_class.description TEXT → LONGTEXT
-- 실행일: 2026-04-14

ALTER TABLE lc_class MODIFY COLUMN description LONGTEXT DEFAULT NULL COMMENT '상세 설명 (HTML)';
