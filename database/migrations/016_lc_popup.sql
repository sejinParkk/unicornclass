-- ============================================================
-- 016_lc_popup.sql
-- 메인 팝업창 테이블 생성
-- ============================================================

CREATE TABLE IF NOT EXISTS lc_popup (
    popup_idx    INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    image_path   VARCHAR(255)     NULL     COMMENT '팝업 이미지 파일명 (storage/uploads/popup/)',
    link_url     VARCHAR(500)     NULL     COMMENT '클릭 시 이동 URL',
    link_target  ENUM('_self','_blank') NOT NULL DEFAULT '_blank' COMMENT '링크 열기 방식',
    start_date   DATE             NULL     COMMENT '노출 시작일 (NULL=제한없음)',
    end_date     DATE             NULL     COMMENT '노출 종료일 (NULL=제한없음)',
    is_active    TINYINT(1)       NOT NULL DEFAULT 1 COMMENT '노출 여부',
    sort_order   SMALLINT         NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    created_at   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME         NULL     ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (popup_idx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='메인 페이지 팝업창';
