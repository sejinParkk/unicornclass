<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class DB
{
    private static ?PDO $instance = null;

    /** 직접 인스턴스화 금지 */
    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $host = $_ENV['DB_HOST'] ?? 'db';
        $name = $_ENV['DB_NAME'] ?? 'unicornclass';
        $user = $_ENV['DB_USER'] ?? 'unicorn';
        $pass = $_ENV['DB_PASS'] ?? '';

        $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

        try {
            self::$instance = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ]);
        } catch (PDOException $e) {
            // 연결 실패: 내부 로그만 기록, 공개 에러 미노출
            error_log('[DB] Connection failed: ' . $e->getMessage());

            $isApi = str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/');
            if ($isApi) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => '서버 오류가 발생했습니다. 잠시 후 다시 시도해주세요.']);
            } else {
                http_response_code(500);
                echo '<!DOCTYPE html><html lang="ko"><head><meta charset="UTF-8"><title>서비스 오류</title>'
                   . '<style>body{font-family:sans-serif;text-align:center;padding:80px;color:#333}'
                   . 'h1{font-size:40px;margin:0}p{font-size:16px;margin:16px 0;color:#666}</style></head><body>'
                   . '<h1>서비스를 일시적으로 사용할 수 없습니다.</h1>'
                   . '<p>잠시 후 다시 시도해주세요.</p></body></html>';
            }
            exit;
        }

        return self::$instance;
    }

    /**
     * 편의 메서드: SELECT 여러 행
     *
     * @return list<array<string, mixed>>
     */
    public static function select(string $sql, array $bindings = []): array
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    /**
     * 편의 메서드: SELECT 단일 행
     *
     * @return array<string, mixed>|null
     */
    public static function selectOne(string $sql, array $bindings = []): ?array
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($bindings);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * 편의 메서드: INSERT / UPDATE / DELETE
     * 영향받은 행 수 반환
     */
    public static function execute(string $sql, array $bindings = []): int
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->rowCount();
    }

    /**
     * 편의 메서드: INSERT 후 last insert ID 반환
     */
    public static function insert(string $sql, array $bindings = []): string
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bindings);
        return $pdo->lastInsertId();
    }

    /** 트랜잭션 래퍼 */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::getInstance();
        $pdo->beginTransaction();
        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
