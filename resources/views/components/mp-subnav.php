<?php
use App\Core\Csrf;

$_mpUri = strtok($_SERVER['REQUEST_URI'], '?');

$_mpNavItems = [
    'my-class' => ['label' => '나의 강의',  'url' => '/mypage/my-class'],
    'wishlist'  => ['label' => '찜 목록',     'url' => '/mypage/wishlist'],
    'orders'    => ['label' => '결제 내역',   'url' => '/mypage/orders'],
    'qna'       => ['label' => '1:1 문의',   'url' => '/mypage/qna'],
    'reviews'   => ['label' => '후기 관리',    'url' => '/mypage/reviews'],
    'profile'   => ['label' => '정보 수정',   'url' => '/mypage/profile'],
];
?>
<nav class="cs-subnav">
  <?php $i = 0; foreach ($_mpNavItems as $key => $_mpItem):
    $i++;
    $_mpActive = str_starts_with($_mpUri, $_mpItem['url']);
  ?>
  <a href="<?= $_mpItem['url'] ?>" class="cs-subnav-item <?= $_mpActive ? 'active' : '' ?>">     
    <img src="/assets/img/mp_subnav_<?php echo $i?>_<?= $_mpActive ? 'on' : 'off' ?>.svg" alt="">
    <span><?= htmlspecialchars($_mpItem['label']) ?></span>
  </a>
  <?php endforeach; ?>
  <button type="button" class="cs-subnav-item" onclick="openModal('logoutModal')">
    <img src="/assets/img/mp_subnav_7_off.svg" alt="">
    <span>로그아웃</span>
  </button>
</nav>
