-- ============================================================
-- 017_lc_banner.sql
-- 메인 이벤트/공지 배너 슬라이더 테이블 생성
-- site_config 키 추가 (kakao_channel_url)
-- ============================================================

CREATE TABLE IF NOT EXISTS lc_banner (
    banner_idx   INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    image_path   VARCHAR(255)     NULL     COMMENT '배너 이미지 파일명 (storage/uploads/banner/)',
    link_url     VARCHAR(500)     NULL     COMMENT '클릭 시 이동 URL',
    link_target  ENUM('_self','_blank') NOT NULL DEFAULT '_blank' COMMENT '링크 열기 방식',
    alt_text     VARCHAR(200)     NOT NULL DEFAULT '' COMMENT '이미지 대체 텍스트',
    start_date   DATE             NULL     COMMENT '노출 시작일 (NULL=제한없음)',
    end_date     DATE             NULL     COMMENT '노출 종료일 (NULL=제한없음)',
    is_active    TINYINT(1)       NOT NULL DEFAULT 1 COMMENT '노출 여부',
    sort_order   SMALLINT         NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    created_at   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME         NULL     ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (banner_idx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='메인 페이지 이벤트/공지 배너 슬라이더';

-- 카카오 채널 URL site_config 키 추가
INSERT IGNORE INTO lc_site_config (config_key, config_value) VALUES
    ('kakao_channel_url', NULL);
