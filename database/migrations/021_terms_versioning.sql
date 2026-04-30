-- 약관 버전 관리: uq_type 제거, effective_at / is_current 추가
ALTER TABLE lc_terms
  DROP INDEX uq_type,
  ADD COLUMN effective_at DATE        NOT NULL DEFAULT '2024-01-01' AFTER content,
  ADD COLUMN is_current   TINYINT(1)  NOT NULL DEFAULT 0            AFTER effective_at;

-- 기존 행을 각 타입의 최초 현재 버전으로 설정
UPDATE lc_terms SET effective_at = COALESCE(DATE(updated_at), CURDATE()), is_current = 1;
