-- 강의 수강시작일 컬럼 추가
-- enroll_start_at: 이 시각 이전이면 사용자 화면에서 '신청 대기 중' 상태로 표시
ALTER TABLE lc_class
    ADD COLUMN enroll_start_at DATETIME NULL DEFAULT NULL
        AFTER sale_end_at;
