-- 강사 소개 / 경력 다중 항목 테이블
-- 기존 lc_instructor.intro, career TEXT 컬럼을 대체

CREATE TABLE lc_instructor_intro (
    intro_idx      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    instructor_idx INT UNSIGNED NOT NULL,
    content        VARCHAR(500) NOT NULL,
    sort_order     SMALLINT DEFAULT 0,
    INDEX idx_instructor (instructor_idx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lc_instructor_career (
    career_idx     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    instructor_idx INT UNSIGNED NOT NULL,
    content        VARCHAR(500) NOT NULL,
    sort_order     SMALLINT DEFAULT 0,
    INDEX idx_instructor (instructor_idx)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 기존 데이터 마이그레이션 (줄바꿈 기준으로 개별 행으로 분리)
-- intro
INSERT INTO lc_instructor_intro (instructor_idx, content, sort_order)
SELECT
    instructor_idx,
    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(intro, '\n', n.n), '\n', -1)) AS content,
    n.n AS sort_order
FROM lc_instructor
JOIN (
    SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
    UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10
) n ON n.n <= 1 + LENGTH(intro) - LENGTH(REPLACE(intro, '\n', ''))
WHERE intro IS NOT NULL AND intro != ''
  AND TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(intro, '\n', n.n), '\n', -1)) != '';

-- career
INSERT INTO lc_instructor_career (instructor_idx, content, sort_order)
SELECT
    instructor_idx,
    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(career, '\n', n.n), '\n', -1)) AS content,
    n.n AS sort_order
FROM lc_instructor
JOIN (
    SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
    UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10
) n ON n.n <= 1 + LENGTH(career) - LENGTH(REPLACE(career, '\n', ''))
WHERE career IS NOT NULL AND career != ''
  AND TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(career, '\n', n.n), '\n', -1)) != '';

-- 기존 컬럼 제거
ALTER TABLE lc_instructor
    DROP COLUMN intro,
    DROP COLUMN career;
