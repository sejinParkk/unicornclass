-- ============================================================
-- 테스트 회원 시드 (개발/시나리오 확인용)
-- 비밀번호: Test1234!
-- password_hash('Test1234!', PASSWORD_BCRYPT, ['cost' => 12])
-- ============================================================

INSERT INTO `lc_member`
    (`mb_id`, `mb_password`, `mb_name`, `mb_phone`, `mb_email`, `signup_type`, `is_active`)
VALUES
    -- 일반 이메일 가입 회원
    (
        'testuser1',
        '$2y$12$LqxMHbqT3WcF8sT4E9Pd3eGkI2qONt6RzdQqCpBJOLHR5H1Cxzxle',
        '홍길동',
        '010-1234-5678',
        'testuser1@test.com',
        'email',
        1
    ),
    -- 카카오 소셜 가입 회원
    (
        'kakao_99999',
        NULL,
        '카카오유저',
        '010-9876-5432',
        'kakao@test.com',
        'kakao',
        1
    )
ON DUPLICATE KEY UPDATE
    `mb_name`  = VALUES(`mb_name`),
    `mb_phone` = VALUES(`mb_phone`);
