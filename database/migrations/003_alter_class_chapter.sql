-- lc_class_chapter: is_active 컬럼 추가 (소프트 삭제용)
ALTER TABLE `lc_class_chapter`
    ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `sort_order`;

-- lc_class: badge_hot, badge_new 컬럼 추가 (doc 스펙 반영)
ALTER TABLE `lc_class`
    ADD COLUMN `badge_hot` TINYINT(1) NOT NULL DEFAULT 0 AFTER `vimeo_url`,
    ADD COLUMN `badge_new` TINYINT(1) NOT NULL DEFAULT 0 AFTER `badge_hot`;
