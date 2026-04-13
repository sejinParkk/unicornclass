<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    /** @var array<string, list<array{pattern: string, params: list<string>, handler: callable|array}>> */
    private array $routes = [];

    /** @var callable|null */
    private $notFoundHandler = null;

    // -------------------------------------------------------------------------
    // 라우트 등록
    // -------------------------------------------------------------------------

    public function get(string $uri, callable|array $handler): void
    {
        $this->add('GET', $uri, $handler);
    }

    public function post(string $uri, callable|array $handler): void
    {
        $this->add('POST', $uri, $handler);
    }

    public function put(string $uri, callable|array $handler): void
    {
        $this->add('PUT', $uri, $handler);
    }

    public function delete(string $uri, callable|array $handler): void
    {
        $this->add('DELETE', $uri, $handler);
    }

    /** PUT/PATCH/DELETE 오버라이드를 위한 편의 메서드 (폼에서 _method 활용) */
    public function any(string $uri, callable|array $handler): void
    {
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $this->add($method, $uri, $handler);
        }
    }

    private function add(string $method, string $uri, callable|array $handler): void
    {
        $params  = [];
        // {param} → 캡처 그룹으로 변환
        $pattern = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            function (array $m) use (&$params): string {
                $params[] = $m[1];
                return '([^/]+)';
            },
            $uri
        );
        $pattern = '#^' . $pattern . '$#';

        $this->routes[$method][] = [
            'pattern' => $pattern,
            'params'  => $params,
            'handler' => $handler,
        ];
    }

    // -------------------------------------------------------------------------
    // 404 핸들러
    // -------------------------------------------------------------------------

    public function setNotFound(callable $handler): void
    {
        $this->notFoundHandler = $handler;
    }

    // -------------------------------------------------------------------------
    // 디스패치
    // -------------------------------------------------------------------------

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // 폼 메서드 오버라이드 (_method=PUT 등)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper($_POST['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $override;
            }
        }

        $uri = $this->parseUri();

        foreach ($this->routes[$method] ?? [] as $route) {
            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            // 매칭된 캡처 그룹을 이름 있는 파라미터로 조합
            array_shift($matches); // 전체 매치 제거
            $params = array_combine($route['params'], $matches) ?: [];

            $this->call($route['handler'], $params);
            return;
        }

        $this->handleNotFound();
    }

    private function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        // 쿼리스트링 제거
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        return '/' . trim(rawurldecode($uri), '/');
    }

    private function call(callable|array $handler, array $params): void
    {
        $args = array_values($params);

        if (is_callable($handler)) {
            call_user_func_array($handler, $args);
            return;
        }

        // [ControllerClass::class, 'method'] 형태
        [$class, $method] = $handler;
        $controller = new $class();
        call_user_func_array([$controller, $method], $args);
    }

    private function handleNotFound(): void
    {
        http_response_code(404);

        if ($this->notFoundHandler !== null) {
            call_user_func($this->notFoundHandler);
            return;
        }

        // 기본 404 응답
        $isApi = str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/');
        if ($isApi) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => '요청한 리소스를 찾을 수 없습니다.']);
        } else {
            echo '<!DOCTYPE html><html lang="ko"><head><meta charset="UTF-8"><title>404 Not Found</title>'
               . '<style>body{font-family:sans-serif;text-align:center;padding:80px;color:#333}'
               . 'h1{font-size:64px;margin:0;color:#c0392b}p{font-size:18px;margin:16px 0}'
               . 'a{color:#c0392b}</style></head><body>'
               . '<h1>404</h1><p>페이지를 찾을 수 없습니다.</p>'
               . '<a href="/">← 메인으로 돌아가기</a></body></html>';
        }
    }
}
