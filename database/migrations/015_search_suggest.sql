-- ============================================================
-- 015_search_suggest.sql
-- 추천 검색어 테이블 생성 및 초기 데이터
-- ============================================================

CREATE TABLE IF NOT EXISTS lc_search_suggest (
    suggest_idx  INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    keyword      VARCHAR(50)      NOT NULL COMMENT '추천 검색어',
    sort_order   SMALLINT         NOT NULL DEFAULT 0 COMMENT '정렬 순서 (낮을수록 앞)',
    is_active    TINYINT(1)       NOT NULL DEFAULT 1 COMMENT '노출 여부',
    created_at   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (suggest_idx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='검색결과 없음 화면의 추천 검색어';

-- 초기 추천 검색어
INSERT INTO lc_search_suggest (keyword, sort_order) VALUES
    ('스마트스토어', 10),
    ('AI 자동화',    20),
    ('유튜브',       30),
    ('인스타그램',   40),
    ('쿠팡',         50);
