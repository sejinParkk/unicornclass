-- 관리자 계정 시드 (login_id: admin / password: Admin1234!)
-- password_hash('Admin1234!', PASSWORD_BCRYPT, ['cost' => 12])
INSERT INTO `lc_admin` (`login_id`, `name`, `password`)
VALUES (
    'admin',
    '관리자',
    '$2y$12$ktbX8xeavVdXDyVhFrcBG.1.zONqnLn.VQ5qXfakATnTQPnIcmJrO'
)
ON DUPLICATE KEY UPDATE
    `password` = VALUES(`password`),
    `name`     = VALUES(`name`);
