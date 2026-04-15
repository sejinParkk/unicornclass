<?php
/**
 * 마이페이지 공통 레이아웃
 *
 * 사용법:
 *   $pageTitle   = '나의 강의 — 유니콘클래스';
 *   $mpActiveNav = 'my-class';   // 사이드바 active 항목 키
 *   ob_start();
 *   // ... 콘텐츠 HTML ...
 *   $mpContent = ob_get_clean();
 *   require VIEW_PATH . '/layout/mypage.php';
 */

use App\Core\Auth;
use App\Core\Csrf;

$_session    = Auth::member();
$_avatarChar = mb_substr($_session['mb_name'], 0, 1);

$_navItems = [
    'my-class'  => ['icon' => '📚', 'label' => '나의 강의',  'url' => '/mypage/my-class'],
    'wishlist'  => ['icon' => '❤️',  'label' => '찜목록',     'url' => '/mypage/wishlist'],
    'orders'    => ['icon' => '💳', 'label' => '결제내역',   'url' => '/mypage/orders'],
    'qna'       => ['icon' => '💬', 'label' => '1:1 문의',   'url' => '/mypage/qna'],
    'reviews'   => ['icon' => '⭐', 'label' => '내 후기',    'url' => '/mypage/reviews'],
    'profile'   => ['icon' => '⚙️',  'label' => '정보수정',   'url' => '/mypage/profile'],
];

require VIEW_PATH . '/layout/header.php';
?>
<main>
<div class="mp-wrap">

  <!-- 사이드바 -->
  <aside class="mp-sidebar">
    <div class="mp-user-area">
      <div class="mp-user-avatar"><?= htmlspecialchars($_avatarChar) ?></div>
      <div class="mp-user-name"><?= htmlspecialchars($_session['mb_name']) ?></div>
      <div class="mp-user-id"><?= htmlspecialchars($_session['mb_id']) ?></div>
    </div>
    <nav class="mp-nav">
      <?php foreach ($_navItems as $key => $item): ?>
        <?php if ($key === 'qna'): ?>
          <div class="mp-nav-divider"></div>
        <?php endif; ?>
        <a href="<?= $item['url'] ?>"
           class="mp-nav-item <?= ($mpActiveNav ?? '') === $key ? 'active' : '' ?>">
          <span class="mp-nav-icon"><?= $item['icon'] ?></span>
          <?= htmlspecialchars($item['label']) ?>
        </a>
      <?php endforeach; ?>
      <div class="mp-nav-divider"></div>
      <form method="POST" action="/logout" style="margin:0">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
        <button type="submit" class="mp-nav-item mp-nav-logout"
                style="width:100%;background:none;border:none;cursor:pointer;font-family:inherit">
          <span class="mp-nav-icon">🚪</span>로그아웃
        </button>
      </form>
    </nav>
  </aside>

  <!-- 본문 콘텐츠 -->
  <div class="mp-content">
    <?= $mpContent ?? '' ?>
  </div>

</div>
</main>
<?php require VIEW_PATH . '/layout/footer.php'; ?>
