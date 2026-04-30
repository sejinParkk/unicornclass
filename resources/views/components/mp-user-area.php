<?php
use App\Core\Auth;
$_mpSession    = Auth::member();
$_mpAvatarChar = mb_substr($_mpSession['mb_name'] ?? '?', 0, 1);
$_mpUri        = strtok($_SERVER['REQUEST_URI'], '?');

$_mpNavItems = [
    ['label' => '나의 강의',  'url' => '/mypage/my-class'],
    ['label' => '찜 목록',    'url' => '/mypage/wishlist'],
    ['label' => '결제 내역',  'url' => '/mypage/orders'],
    ['label' => '1:1 문의',   'url' => '/mypage/qna'],
    ['label' => '후기 관리',  'url' => '/mypage/reviews'],
    ['label' => '정보 수정',  'url' => '/mypage/profile'],
];
?>
<div class="mp-user-area">
  <div class="mp-user-avatar"><?= htmlspecialchars($_mpAvatarChar) ?></div>
  <div class="mp-user-info">
    <div class="mp-user-name"><?= htmlspecialchars($_mpSession['mb_name'] ?? '') ?></div>
    <div class="mp-user-id"><?= htmlspecialchars($_mpSession['mb_id'] ?? '') ?></div>
  </div>
</div>

<nav class="mp-mob-nav">
  <?php foreach ($_mpNavItems as $_item): ?>
  <a href="<?= $_item['url'] ?>"
     class="mp-mob-item <?= str_starts_with($_mpUri, $_item['url']) ? 'active' : '' ?>">
    <?= htmlspecialchars($_item['label']) ?>
  </a>
  <?php endforeach; ?>
  <button type="button" class="mp-mob-item" onclick="openModal('logoutModal')">로그아웃</button>
</nav>
