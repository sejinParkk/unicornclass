<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>관리자 로그인 — 유니콘클래스</title>
	<link rel="stylesheet" href="/assets/css/styles.css">
	<style>
		body.auth-page { min-height:100vh; display:flex; flex-direction:column; justify-content:center; }
		body.auth-page main { padding:40px 20px; }
	</style>
</head>
<body class="auth-page">
<main>
<div class="auth-card">
	<div class="auth-logo">
		<img src="/assets/img/logo2.svg" alt="유니콘클래스">
	</div>

	<p style="text-align:center;font-size:13px;color:#8898aa;margin:-28px 0 28px;">관리자 전용 페이지</p>

	<?php if (!empty($error)): ?>
	<div class="alert-error" style="background:#fff5f5;border:1px solid #fc8181;border-radius:8px;padding:12px 14px;font-size:13px;color:#c53030;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
		<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
				  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
		</svg>
		<?= htmlspecialchars($error) ?>
	</div>
	<?php endif; ?>

	<form method="POST" action="/admin/login" id="adminLoginForm" novalidate>
		<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

		<div class="auth-input-group" id="idGroup">
			<input type="text" name="mb_id" id="mb_id"
				value="<?= htmlspecialchars($_POST['mb_id'] ?? '') ?>"
				placeholder="관리자 아이디"
				autocomplete="username"
				autofocus>
		</div>
		<div class="auth-input-group" id="pwGroup">
			<input type="password" name="mb_password" id="mb_password"
				placeholder="비밀번호"
				autocomplete="current-password">
		</div>

		<button type="submit" class="btn-next mgt24" id="loginBtn">로그인</button>
	</form>

	<div class="auth-links">
		<a href="/">사이트로 돌아가기</a>
	</div>
</div>
</main>

<script>
document.getElementById('adminLoginForm').addEventListener('submit', function() {
	const btn = document.getElementById('loginBtn');
	btn.disabled = true;
	btn.textContent = '로그인 중...';
});
</script>
</body>
</html>
