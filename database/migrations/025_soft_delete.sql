-- 025_soft_delete.sql
-- lc_qna, lc_banner, lc_faq, lc_popup, lc_terms, lc_review_image 소프트 삭제 컬럼 추가

ALTER TABLE lc_qna
  ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT '소프트 삭제일시' AFTER `updated_at`;

ALTER TABLE lc_banner
  ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT '소프트 삭제일시' AFTER `created_at`;

ALTER TABLE lc_faq
  ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT '소프트 삭제일시';

ALTER TABLE lc_popup
  ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT '소프트 삭제일시' AFTER `created_at`;

ALTER TABLE lc_terms
  ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT '소프트 삭제일시' AFTER `updated_at`;

ALTER TABLE lc_review_image
  ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL COMMENT '소프트 삭제일시';
