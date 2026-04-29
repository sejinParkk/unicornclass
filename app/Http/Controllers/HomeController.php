<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\HomeRepository;

class HomeController
{
    private HomeRepository $repo;

    public function __construct()
    {
        $this->repo = new HomeRepository();
    }

    // =========================================================================
    // GET /
    // =========================================================================
    public function index(): void
    {
        // 강의
        $freeClasses    = $this->repo->getHomeClasses('free',    6);
        $premiumClasses = $this->repo->getHomeClasses('premium', 6);

        // 강사
        $instructors = $this->repo->getHomeInstructors(8);

        // 후기
        $reviews = $this->repo->getHomeReviews(6);

        // 이벤트 배너 (테이블 미생성 시 빈 배열)
        try {
            $banners = $this->repo->getActiveBanners();
        } catch (\Throwable $e) {
            error_log('[Home] 배너 조회 실패: ' . $e->getMessage());
            $banners = [];
        }

        // 팝업 (테이블 미생성 시 빈 배열)
        try {
            $popups = $this->repo->getActivePopups();
        } catch (\Throwable $e) {
            error_log('[Home] 팝업 조회 실패: ' . $e->getMessage());
            $popups = [];
        }

        // 사이트 설정
        $settings = $this->repo->getSiteSettings(['hero_video', 'kakao_channel_url']);
        $heroVideo        = $settings['hero_video']        ?? null;
        $kakaoChannelUrl  = $settings['kakao_channel_url'] ?? null;

        $pageTitle   = '유니콘클래스 - 온라인 강의 플랫폼';
        $extraStyles = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">'
                     . '<link rel="stylesheet" href="/assets/css/home.css">';

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/home.php';
        require VIEW_PATH . '/layout/footer.php';
    }
}
