<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>관리자 로그인 — 유니콘클래스</title>
	<style>
		*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
		body {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans KR', sans-serif;
			background: #1e2a3a;
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.login-wrap {
			width: 100%;
			max-width: 400px;
			padding: 20px;
		}

		/* Logo */
		.login-logo {
			text-align: center;
			margin-bottom: 32px;
		}
		.logo-icon {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 56px; height: 56px;
			background: #c0392b;
			border-radius: 16px;
			font-size: 24px;
			font-weight: 800;
			color: #fff;
			margin-bottom: 14px;
		}
		.login-logo h1 {
			font-size: 20px;
			font-weight: 700;
			color: #fff;
			margin-bottom: 4px;
		}
		.login-logo p {
			font-size: 13px;
			color: rgba(255,255,255,0.45);
		}

		/* Card */
		.login-card {
			background: #fff;
			border-radius: 16px;
			padding: 36px 32px;
			box-shadow: 0 20px 60px rgba(0,0,0,0.3);
		}

		.login-card h2 {
			font-size: 18px;
			font-weight: 700;
			color: #1a202c;
			margin-bottom: 6px;
		}
		.login-card .subtitle {
			font-size: 13px;
			color: #8898aa;
			margin-bottom: 28px;
		}

		/* Form */
		.form-group { margin-bottom: 18px; }
		.form-label {
			display: block;
			font-size: 12.5px;
			font-weight: 600;
			color: #4a5568;
			margin-bottom: 6px;
		}
		.form-control {
			width: 100%;
			padding: 10px 14px;
			border: 1.5px solid #e2e8f0;
			border-radius: 8px;
			font-size: 14px;
			color: #2c3e50;
			transition: border-color 0.2s, box-shadow 0.2s;
			outline: none;
			background: #fafafa;
		}
		.form-control:focus {
			border-color: #c0392b;
			box-shadow: 0 0 0 3px rgba(192,57,43,0.12);
			background: #fff;
		}
		.form-control::placeholder { color: #b0bec5; }

		/* Error */
		.alert-error {
			background: #fff5f5;
			border: 1px solid #fc8181;
			border-radius: 8px;
			padding: 12px 14px;
			font-size: 13px;
			color: #c53030;
			margin-bottom: 20px;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		/* Submit */
		.btn-login {
			width: 100%;
			padding: 12px;
			background: #c0392b;
			color: #fff;
			border: none;
			border-radius: 8px;
			font-size: 15px;
			font-weight: 600;
			cursor: pointer;
			transition: background 0.15s, transform 0.1s;
			margin-top: 4px;
		}
		.btn-login:hover { background: #a93226; }
		.btn-login:active { transform: scale(0.99); }
		.btn-login:disabled { background: #e2e8f0; color: #a0aec0; cursor: not-allowed; }

		/* Footer */
		.login-footer {
			text-align: center;
			margin-top: 20px;
			font-size: 12px;
			color: rgba(255,255,255,0.3);
		}

		/* Loading state */
		.btn-login.loading { pointer-events: none; opacity: 0.75; }
	</style>
</head>
<body>
<div class="login-wrap">
	<div class="login-logo">
		<div class="logo-icon">U</div>
		<h1>유니콘클래스</h1>
		<p>관리자 전용 페이지</p>
	</div>

	<div class="login-card">
		<h2>관리자 로그인</h2>
		<p class="subtitle">관리자 계정으로 로그인하세요.</p>

		<?php if (!empty($error)): ?>
		<div class="alert-error">
			<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
					  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
			</svg>
			<?= htmlspecialchars($error) ?>
		</div>
		<?php endif; ?>

		<form id="loginForm" method="POST" action="/admin/login" novalidate>
			<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

			<div class="form-group">
				<label class="form-label" for="mb_id">아이디</label>
				<input
					type="text"
					id="mb_id"
					name="mb_id"
					class="form-control"
					placeholder="관리자 아이디"
					value="<?= htmlspecialchars($_POST['mb_id'] ?? '') ?>"
					autocomplete="username"
					required
					autofocus
				>
			</div>

			<div class="form-group">
				<label class="form-label" for="mb_password">비밀번호</label>
				<input
					type="password"
					id="mb_password"
					name="mb_password"
					class="form-control"
					placeholder="비밀번호"
					autocomplete="current-password"
					required
				>
			</div>

			<button type="submit" class="btn-login" id="submitBtn">로그인</button>
		</form>
	</div>

	<p class="login-footer">© <?= date('Y') ?> Unicorn Class. All rights reserved.</p>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function () {
	const btn = document.getElementById('submitBtn');
	btn.disabled = true;
	btn.textContent = '로그인 중...';
	btn.classList.add('loading');
});
</script>
</body>
</html>
