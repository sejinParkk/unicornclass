<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\FaqRepository;
use App\Repositories\NoticeRepository;
use App\Repositories\SettingRepository;

class SupportController
{
    private FaqRepository     $faqRepo;
    private NoticeRepository  $noticeRepo;
    private SettingRepository $settingRepo;

    public function __construct()
    {
        $this->faqRepo     = new FaqRepository();
        $this->noticeRepo  = new NoticeRepository();
        $this->settingRepo = new SettingRepository();
    }

    // =========================================================================
    // GET /supports/faqs
    // =========================================================================
    public function faqs(): void
    {
        $category   = $_GET['category'] ?? 'all';
        $categories = $this->faqRepo->getCategories();
        $faqs       = $this->faqRepo->getList($category !== 'all' ? $category : '');

        $pageTitle = 'FAQ - 고객센터';
        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/supports/faqs.php';
        require VIEW_PATH . '/layout/footer.php';
    }

    // =========================================================================
    // GET /supports/notices
    // =========================================================================
    public function notices(): void
    {
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $limit  = 10;
        $result = $this->noticeRepo->getPublicList($page, $limit);
        $list   = $result['list'];
        $total  = $result['total'];
        $pages  = (int) ceil($total / $limit);

        $pageTitle = '공지사항 - 고객센터';
        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/supports/notices.php';
        require VIEW_PATH . '/layout/footer.php';
    }

    // =========================================================================
    // GET /supports/notices/{notice_idx}
    // =========================================================================
    public function noticeShow(string $notice_idx): void
    {
        $noticeIdx = (int) $notice_idx;
        $notice    = $this->noticeRepo->findPublicByIdx($noticeIdx);

        if (!$notice) {
            http_response_code(404);
            exit('공지사항을 찾을 수 없습니다.');
        }

        // 조회수 증가 (세션으로 중복 방지)
        $sessionKey = 'notice_viewed_' . $noticeIdx;
        if (empty($_SESSION[$sessionKey])) {
            $this->noticeRepo->incrementViews($noticeIdx);
            $_SESSION[$sessionKey] = time();
        }

        $prevNext  = $this->noticeRepo->getPrevNext($noticeIdx);
        $pageTitle = htmlspecialchars($notice['title']) . ' - 공지사항';

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/supports/notice-show.php';
        require VIEW_PATH . '/layout/footer.php';
    }

    // =========================================================================
    // GET /supports/terms
    // =========================================================================
    public function terms(): void
    {
        $term      = $this->settingRepo->getTerm('terms');
        $ajaxMode  = !empty($_GET['ajax']);
        $pageTitle = '이용약관 - 고객센터';

        if ($ajaxMode) {
            require VIEW_PATH . '/pages/supports/terms.php';
            exit;
        }

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/supports/terms.php';
        require VIEW_PATH . '/layout/footer.php';
    }

    // =========================================================================
    // GET /supports/privacy
    // =========================================================================
    public function privacy(): void
    {
        $term      = $this->settingRepo->getTerm('privacy');
        $ajaxMode  = !empty($_GET['ajax']);
        $pageTitle = '개인정보처리방침 - 고객센터';

        if ($ajaxMode) {
            require VIEW_PATH . '/pages/supports/privacy.php';
            exit;
        }

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/supports/privacy.php';
        require VIEW_PATH . '/layout/footer.php';
    }

    // =========================================================================
    // GET /supports/policy/{type}   (generic — ajax=1 지원)
    // =========================================================================
    private const POLICY_TYPES = [
        'marketing'     => '마케팅 수신 동의',
        'disclaimer'    => '면책조항',
        'purchase'      => '구매 조건 동의',
        'ecommerce'     => '전자금융거래 이용약관',
        'privacy_third' => '개인정보 제3자 제공 동의 (PG사)',
    ];

    public function policy(string $type): void
    {
        if (!array_key_exists($type, self::POLICY_TYPES)) {
            http_response_code(404);
            exit;
        }

        $term      = $this->settingRepo->getTerm($type);
        $policyTitle = self::POLICY_TYPES[$type];
        $ajaxMode  = !empty($_GET['ajax']);
        $pageTitle = $policyTitle . ' - 고객센터';

        if ($ajaxMode) {
            require VIEW_PATH . '/pages/supports/policy.php';
            exit;
        }

        require VIEW_PATH . '/layout/header.php';
        require VIEW_PATH . '/pages/supports/policy.php';
        require VIEW_PATH . '/layout/footer.php';
    }

    // =========================================================================
    // 1:1 문의 (마이페이지로 리다이렉트)
    // =========================================================================
    public function contactList(): void
    {
        header('Location: /mypage/qna');
        exit;
    }

    public function contactForm(): void
    {
        header('Location: /mypage/qna');
        exit;
    }

    public function contactStore(): void
    {
        header('Location: /mypage/qna');
        exit;
    }

    public function contactShow(string $qna_idx): void
    {
        header('Location: /mypage/qna/' . (int) $qna_idx);
        exit;
    }
}
