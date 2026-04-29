<?php
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;

// 사이트 설정
$_site       = DB::selectOne("SELECT config_value FROM lc_site_config WHERE config_key = 'site_name'");
$_siteName   = $_site['config_value'] ?? '유니콘클래스';

$_logo       = DB::selectOne("SELECT config_value FROM lc_site_config WHERE config_key = 'logo'");
$_logoFile   = $_logo['config_value'] ?? '';

$_favicon    = DB::selectOne("SELECT config_value FROM lc_site_config WHERE config_key = 'favicon'");
$_faviconFile = $_favicon['config_value'] ?? '';

// 회원 정보
$_member     = Auth::isMember() ? Auth::member() : null;
$_avatarChar = $_member ? mb_substr($_member['mb_name'], 0, 1) : '';

// 장바구니 수량 (lc_cart 구현 후 활성화)
$_cartCount  = 0;

// 로그아웃 CSRF
$_logoutCsrf = $_member ? Csrf::token() : '';

// 현재 URI로 active 메뉴 판단
$_uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$_navActive = function(string $prefix) use ($_uri): string {
  return str_starts_with($_uri, $prefix) ? 'active' : '';
};
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? $_siteName) ?></title>
  <?php if ($_faviconFile): ?>
  <link rel="icon" href="/uploads/site/<?= htmlspecialchars($_faviconFile) ?>">
  <?php endif; ?>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />
  <link rel="stylesheet" href="/assets/css/noto-sans.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
  <link rel="stylesheet" href="/assets/css/styles.css">
  <?php if (!empty($extraStyles)) echo $extraStyles; ?>
</head>
<body<?= !empty($bodyClass) ? ' class="' . htmlspecialchars($bodyClass) . '"' : '' ?>>


<!-- ====== HEADER ====== -->
<div id="site-header">
  <div class="header-inner">
    <div class="header-left">
      <!-- 로고 -->
      <div class="logo">
        <a href="/">
          <img src="/assets/img/logo.svg" alt="<?= htmlspecialchars($_siteName) ?>" class="logo-img">
        </a>
      </div>
      <!-- 검색 -->
      <form class="search-box" action="/search" method="GET">
        <button type="submit"><img src="/assets/img/hd_sch.svg"></button>
        <input type="text" name="q" placeholder="강사 / 과정명을 입력해 주세요." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">        
      </form>
    </div>

    <!-- GNB -->
    <nav class="gnb">
      <div class="gnb-item <?= $_navActive('/classes') ?>">
        <a href="/classes"><span>클래스</span></a>
        <div class="gnb-dropdown">
          <a href="/classes?type=free">무료강의</a>
          <a href="/classes?type=premium">프리미엄강의</a>
        </div>
      </div>
      <div class="gnb-item <?= $_navActive('/about') ?>">
        <a href="/about"><span>회사소개</span></a>
      </div>
      <div class="gnb-item <?= $_navActive('/instructors') ?>">
        <a href="/instructors"><span>강사진</span></a>
      </div>
      <div class="gnb-item <?= $_navActive('/supports') ?>">
        <a href="/supports/faqs"><span>고객센터</span></a>
        <div class="gnb-dropdown">
          <a href="/supports/faqs">FAQ</a>
          <a href="/supports/notices">공지사항</a>          
        </div>
      </div>
    </nav>

    <!-- 우측 액션 -->
    <div class="header-right">
      <a href="/instructors/apply" class="header_right_btn header_right_req">강사 지원하기</a>
      
      <?php if ($_member): ?>

      <!-- 마이페이지 드롭다운 -->
      <div class="mypage-wrap">
        <div class="header_right_btn header_right_my">
          <div class="mypage-avatar"><?= htmlspecialchars($_avatarChar) ?></div>
          <p>
            <span><?= htmlspecialchars($_member['mb_name']) ?></span>
            <img src="/assets/img/hd_my_btn_arr.svg" alt="">
          </p>
        </div>
        <div class="mypage-dropdown">
          <div class="mypage-dropdown-inner">
            <a href="/mypage/my-class"  class="<?= $_navActive('/mypage/my-class')  ?>">나의 강의</a>
            <a href="/mypage/wishlist"  class="<?= $_navActive('/mypage/wishlist')   ?>">찜목록</a>
            <a href="/mypage/orders"    class="<?= $_navActive('/mypage/orders')     ?>">결제내역</a>
            <a href="/mypage/qna"       class="<?= $_navActive('/mypage/qna')        ?>">1:1 문의</a>
            <a href="/mypage/reviews"   class="<?= $_navActive('/mypage/reviews')    ?>">내 후기</a>          
            <a href="/mypage/profile"   class="<?= $_navActive('/mypage/profile')    ?>">정보수정</a>
            <button type="button" class="md-logout" onclick="openModal('logoutModal')">로그아웃</button>
          </div>
        </div>
      </div>

      <?php else: ?>
      <a href="/login" class="header_right_btn header_right_login">로그인/회원가입</a>
      <?php endif; ?>

      <!-- 모바일 햄버거 -->
      <button class="header-hamburger" id="hamburgerBtn" aria-label="메뉴 열기">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</div>

<!-- ====== 모바일 드로어 ====== -->
<div class="mobile-drawer" id="mobileDrawer">
    <div class="drawer-overlay" onclick="closeDrawer()"></div>
    <div class="drawer-body">
        <button class="drawer-close" onclick="closeDrawer()">×</button>
        <a href="/classes">클래스</a>
        <a href="/classes?type=free" class="drawer-sub">└ 무료강의</a>
        <a href="/classes?type=premium" class="drawer-sub">└ 프리미엄강의</a>
        <a href="/about">회사소개</a>
        <a href="/instructors">강사진</a>
        <a href="/supports/faqs">고객센터</a>
        <a href="/supports/faqs" class="drawer-sub">└ FAQ</a>
        <a href="/supports/notices" class="drawer-sub">└ 공지사항</a>        
        <a href="/instructors/apply">🎓 강사 지원하기</a>
        <a href="/search">🔍 검색</a>
        <div class="drawer-auth">
            <?php if ($_member): ?>
            <a href="/mypage/my-class">📚 나의 강의</a>
            <a href="/mypage/wishlist">❤️ 찜목록</a>
            <a href="/mypage/orders">💳 결제내역</a>
            <a href="/mypage/qna">💬 1:1 문의</a>
            <a href="/mypage/profile">⚙️ 정보수정</a>
            <button type="button" class="drawer-btn-login" style="width:100%;cursor:pointer" onclick="closeDrawer();openModal('logoutModal')">🚪 로그아웃</button>
            <?php else: ?>
            <a href="/login" class="drawer-btn-login">로그인</a>
            <a href="/register" class="drawer-btn-mypage">회원가입</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('hamburgerBtn').addEventListener('click', function() {
    document.getElementById('mobileDrawer').classList.add('open');
    document.body.style.overflow = 'hidden';
});
function closeDrawer() {
    document.getElementById('mobileDrawer').classList.remove('open');
    document.body.style.overflow = '';
}
function openModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.style.display = 'flex';
  // document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.style.display = 'none';
  // document.body.style.overflow = '';
}
</script>

<!-- ====== PAGE CONTENT START ====== -->
<main>
