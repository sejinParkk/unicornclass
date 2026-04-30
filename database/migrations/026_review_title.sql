-- 026_review_title.sql
-- 후기 제목 컬럼 추가

ALTER TABLE lc_review
  ADD COLUMN `title` VARCHAR(200) NULL DEFAULT NULL COMMENT '후기 제목' AFTER `rating`;
