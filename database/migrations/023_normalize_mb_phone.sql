-- 023_normalize_mb_phone.sql
-- mb_phone 형식 통일: 하이픈 없는 11자리 → 010-XXXX-XXXX 형식으로 변환

UPDATE lc_member
SET mb_phone = CONCAT(
    SUBSTRING(mb_phone, 1, 3), '-',
    SUBSTRING(mb_phone, 4, 4), '-',
    SUBSTRING(mb_phone, 8)
)
WHERE mb_phone IS NOT NULL
  AND mb_phone NOT LIKE '%-%'
  AND CHAR_LENGTH(mb_phone) = 11;

-- 10자리(지역번호 등) 처리
UPDATE lc_member
SET mb_phone = CONCAT(
    SUBSTRING(mb_phone, 1, 3), '-',
    SUBSTRING(mb_phone, 4, 3), '-',
    SUBSTRING(mb_phone, 7)
)
WHERE mb_phone IS NOT NULL
  AND mb_phone NOT LIKE '%-%'
  AND CHAR_LENGTH(mb_phone) = 10;
