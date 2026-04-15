<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\SearchRepository;

class SearchController
{
    private SearchRepository $repo;

    public function __construct()
    {
        $this->repo = new SearchRepository();
    }

    // =========================================================================
    // GET /search?q={keyword}
    // =========================================================================
    public function index(): void
    {
        $q = trim($_GET['q'] ?? '');

        // 빈 검색어 → 전체 클래스 목록으로 이동
        if ($q === '') {
            header('Location: /classes');
            exit;
        }

        // 검색 실행
        $result      = $this->repo->search($q);
        $classes     = $result['classes'];
        $instructors = $result['instructors'];
        $total       = $result['total'];

        // 검색 로그 기록
        try {
            $this->repo->logSearch($q, $total);
        } catch (\Throwable $e) {
            error_log('[SearchLog] 검색 로그 기록 실패: ' . $e->getMessage());
        }

        // 결과 없음일 때 추천 검색어 조회
        $suggestions = [];
        if ($total === 0) {
            try {
                $suggestions = $this->repo->getSuggestions();
            } catch (\Throwable $e) {
                error_log('[SearchSuggest] 추천 검색어 조회 실패: ' . $e->getMessage());
            }
        }

        $pageTitle = '"' . htmlspecialchars($q) . '" 검색결과 - 유니콘클래스';

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/search/index.php';
        require VIEW_PATH . '/layout/footer.php';
    }
}
