<?php
$_favicon = \App\Core\DB::selectOne("SELECT config_value FROM lc_site_config WHERE config_key = 'favicon'")['config_value'] ?? '';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= htmlspecialchars($pageTitle ?? '관리자') ?> — 유니콘클래스 관리자</title>
	<?php if ($_favicon): ?>
	<link rel="icon" href="/uploads/site/<?= htmlspecialchars($_favicon) ?>">
	<?php endif; ?>
	<link rel="stylesheet" href="/assets/css/noto-sans.css">
	<link rel="stylesheet" href="/assets/css/admin.css">
	<?php if (!empty($extraStyles)): ?>
			<?= $extraStyles ?>
	<?php endif; ?>
</head>
<body>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
	<div class="sidebar-logo">유니콘클래스</div>
	</div>

	<?php
	$_m = $activeMenu ?? '';
	$_grpContent  = in_array($_m, ['classes', 'instructors', 'instructor-apply']);
	$_grpOps      = in_array($_m, ['members', 'orders', 'contacts']);
	$_grpBoard    = in_array($_m, ['notices', 'faqs']);
	$_grpStats    = in_array($_m, ['search-logs', 'openchat-logs']);
	$_grpSettings = in_array($_m, ['settings', 'terms', 'profile']);
	?>
	<nav class="sidebar-nav">
		<!-- 대시보드 (단독) -->
		<a href="/admin" class="nav-item <?= $_m === 'dashboard' ? 'active' : '' ?>">
			<svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
			</svg>
			대시보드
		</a>

		<!-- 콘텐츠 그룹 -->
		<div class="nav-group <?= $_grpContent ? 'open' : '' ?>">
			<div class="nav-group-header <?= $_grpContent ? 'active' : '' ?>">
				<div class="nav-group-header-inner">
					<svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
					</svg>
					콘텐츠
				</div>
				<svg class="nav-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
				</svg>
			</div>
			<div class="nav-group-body">
				<a href="/admin/classes" class="nav-sub-item <?= $_m === 'classes' ? 'active' : '' ?>">강의 관리</a>
				<a href="/admin/instructors" class="nav-sub-item <?= $_m === 'instructors' ? 'active' : '' ?>">강사 관리</a>
				<a href="/admin/instructor-apply" class="nav-sub-item <?= $_m === 'instructor-apply' ? 'active' : '' ?>">강사 지원</a>
			</div>
		</div>

		<!-- 운영 그룹 -->
		<div class="nav-group <?= $_grpOps ? 'open' : '' ?>">
			<div class="nav-group-header <?= $_grpOps ? 'active' : '' ?>">
				<div class="nav-group-header-inner">
					<svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
					</svg>
					운영
				</div>
				<svg class="nav-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
				</svg>
			</div>
			<div class="nav-group-body">
				<a href="/admin/members" class="nav-sub-item <?= $_m === 'members' ? 'active' : '' ?>">회원 관리</a>
				<a href="/admin/orders" class="nav-sub-item <?= $_m === 'orders' ? 'active' : '' ?>">결제 관리</a>
				<a href="/admin/contacts" class="nav-sub-item <?= $_m === 'contacts' ? 'active' : '' ?>">1:1 문의</a>
			</div>
		</div>

		<!-- 게시판 그룹 -->
		<div class="nav-group <?= $_grpBoard ? 'open' : '' ?>">
			<div class="nav-group-header <?= $_grpBoard ? 'active' : '' ?>">
				<div class="nav-group-header-inner">
					<svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
					</svg>
					게시판
				</div>
				<svg class="nav-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
				</svg>
			</div>
			<div class="nav-group-body">
				<a href="/admin/notices" class="nav-sub-item <?= $_m === 'notices' ? 'active' : '' ?>">공지사항</a>
				<a href="/admin/faqs" class="nav-sub-item <?= $_m === 'faqs' ? 'active' : '' ?>">FAQ</a>
			</div>
		</div>

		<!-- 통계 그룹 -->
		<div class="nav-group <?= $_grpStats ? 'open' : '' ?>">
			<div class="nav-group-header <?= $_grpStats ? 'active' : '' ?>">
				<div class="nav-group-header-inner">
					<svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
					</svg>
					통계
				</div>
				<svg class="nav-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
				</svg>
			</div>
			<div class="nav-group-body">
				<a href="/admin/search-logs" class="nav-sub-item <?= $_m === 'search-logs' ? 'active' : '' ?>">검색 로그</a>
				<a href="/admin/openchat-logs" class="nav-sub-item <?= $_m === 'openchat-logs' ? 'active' : '' ?>">오픈채팅 통계</a>
			</div>
		</div>

		<!-- 설정 그룹 -->
		<div class="nav-group <?= $_grpSettings ? 'open' : '' ?>">
			<div class="nav-group-header <?= $_grpSettings ? 'active' : '' ?>">
				<div class="nav-group-header-inner">
					<svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
					</svg>
					설정
				</div>
				<svg class="nav-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
				</svg>
			</div>
			<div class="nav-group-body">
				<a href="/admin/settings" class="nav-sub-item <?= $_m === 'settings' ? 'active' : '' ?>">사이트 설정</a>
				<a href="/admin/terms" class="nav-sub-item <?= $_m === 'terms' ? 'active' : '' ?>">약관 관리</a>
				<a href="/admin/profile" class="nav-sub-item <?= $_m === 'profile' ? 'active' : '' ?>">관리자 프로필</a>
			</div>
		</div>
	</nav>

	<div class="sidebar-footer">
		<div class="sidebar-user">
			<div class="avatar"><?= htmlspecialchars(mb_substr($_SESSION['_admin']['name'] ?? 'A', 0, 1)) ?></div>
			<div>
				<div class="user-name"><?= htmlspecialchars($_SESSION['_admin']['name'] ?? '관리자') ?></div>
				<div style="font-size:11px;color:#333;">Administrator</div>
			</div>
		</div>
		<a href="/admin/logout" class="sidebar-logout">로그아웃</a>
	</div>
</aside>

<!-- Header -->
<header class="header" id="header">
	<div class="header-left">
		<button class="hamburger" id="hamburger" aria-label="메뉴 열기"><span></span><span></span><span></span></button>
		<div class="breadcrumb">
			<?php
			$_groupLabel = match(true) {
				$_grpContent  => '콘텐츠',
				$_grpOps      => '운영',
				$_grpBoard    => '게시판',
				$_grpStats    => '통계',
				$_grpSettings => '설정',
				default       => '',
			};
			?>
			<?php if ($_groupLabel): ?>
				<?= $_groupLabel ?>
				<span class="breadcrumb-sep"><img src="/assets/img/navi_arr.png" alt=""></span>
				<span><?= htmlspecialchars($pageTitle ?? '대시보드') ?></span>
			<?php else: ?>
				<span><?= htmlspecialchars($pageTitle ?? '대시보드') ?></span>
			<?php endif; ?>
		</div>
	</div>
	<div class="header-right">
		<a href="/admin/contacts" class="header-badge" title="미답변 문의">
			<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
			</svg>
			<?php if (!empty($pendingContacts)): ?><span class="badge-dot"></span><?php endif; ?>
		</a>
		<div class="header-avatar"><?= htmlspecialchars(mb_substr($_SESSION['_admin']['name'] ?? 'A', 0, 1)) ?></div>
	</div>
</header>

<!-- Main -->
<main class="main-wrapper">
	<div class="main-content">
		<?php if (!empty($pageTitle)): ?>
		<div class="page-header">
			<h1 class="page-title"><?= htmlspecialchars($pageTitle) ?></h1>
			<?php if (!empty($pageDesc)): ?><p class="page-desc"><?= htmlspecialchars($pageDesc) ?></p><?php endif; ?>
		</div>
		<?php endif; ?>

		<?= $content ?? '' ?>
	</div>
</main>

<script>
	// Accordion
	document.querySelectorAll('.nav-group-header').forEach(function (header) {
		header.addEventListener('click', function () {
			var group = this.closest('.nav-group');
			var isOpen = group.classList.contains('open');
			// 다른 그룹 닫기
			document.querySelectorAll('.nav-group.open').forEach(function (g) {
				g.classList.remove('open');
			});
			if (!isOpen) group.classList.add('open');
		});
	});

	(function () {
		const sidebar  = document.getElementById('sidebar');
		const overlay  = document.getElementById('sidebarOverlay');
		const hamburger = document.getElementById('hamburger');

		function openSidebar() {
			sidebar.classList.add('open');
			overlay.classList.add('open');
			document.body.style.overflow = 'hidden';
		}
		function closeSidebar() {
			sidebar.classList.remove('open');
			overlay.classList.remove('open');
			document.body.style.overflow = '';
		}

		hamburger.addEventListener('click', () => {
			sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
		});
		overlay.addEventListener('click', closeSidebar);

		// ESC key
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape') closeSidebar();
		});
	})();
</script>
<?php if (!empty($extraScripts)): ?>
  <?= $extraScripts ?>
<?php endif; ?>
</body>
</html>
