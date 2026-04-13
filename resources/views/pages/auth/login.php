<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>로그인 — 유니콘클래스</title>
  <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="auth-page">
<div class="auth-card">
	<div class="auth-logo">
		<div class="auth-logo-placeholder">
			<div class="auth-logo-icon"><span>UNICORN<br>CLASS</span></div>
			<div class="auth-logo-text">
				<span class="l1">UNICORN</span>
				<span class="l2">CLASS</span>
			</div>
		</div>
	</div>

	<?php if (!empty($_GET['reset'])): ?>
	<div class="alert-success">비밀번호가 변경되었습니다. 새 비밀번호로 로그인해주세요.</div>
	<?php endif; ?>

	<!-- 소셜 버튼 (키 미설정으로 비활성) -->
	<div class="social-btns">
		<button class="btn-social btn-kakao" disabled title="준비 중">
			<div class="social-icon-k">💬</div>
			카카오로 3초만에 시작하기
			<span class="badge-soon">준비중</span>
		</button>
		<button class="btn-social btn-naver" disabled title="준비 중">
			<div class="social-icon-n">N</div>
			네이버로 시작하기
			<span class="badge-soon">준비중</span>
		</button>
	</div>

  <div class="auth-divider"><span>또는 아이디로 로그인</span></div>

	<form method="POST" action="/login" id="loginForm" novalidate>
		<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
		<?php if (!empty($_GET['redirect'])): ?>
		<input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect']) ?>">
		<?php endif; ?>

		<div class="auth-input-group" id="idGroup">
			<input type="text" name="mb_id" id="mb_id"
				value="<?= htmlspecialchars($_POST['mb_id'] ?? '') ?>"
				placeholder="아이디를 입력해주세요"
				autocomplete="username">
			<div class="error-msg" id="idError" style="display:none"></div>
		</div>
		<div class="auth-input-group" id="pwGroup">
			<input type="password" name="mb_password" id="mb_password"
				placeholder="비밀번호를 입력해주세요"
				autocomplete="current-password">
			<div class="error-msg" id="pwError" style="display:none"></div>
		</div>
		<button type="submit" class="btn-next" id="loginBtn" style="margin-top:6px">로그인</button>
	</form>

	<div class="auth-links">
		<a href="/find-id">아이디 찾기</a>
		<a href="/find-password">비밀번호 찾기</a>
		<a href="/register">일반회원 가입하기</a>
	</div>
</div>

<!-- 서버 에러 모달 -->
<?php if (!empty($error)): ?>
<div class="auth-modal-overlay show" id="errorModal">
	<div class="auth-modal-card">
		<button class="auth-modal-close" onclick="closeModal()">×</button>
		<div class="auth-modal-title">
			<?php if (str_contains($error, '탈퇴') || str_contains($error, '정지')): ?>
			이미 탈퇴한 계정입니다.
			<?php else: ?>
			일치하는 정보가 없습니다.
			<?php endif; ?>
		</div>
		<div class="auth-modal-desc">
			<?php if (str_contains($error, '탈퇴') || str_contains($error, '정지')): ?>
			해당 계정은 이용하실 수 없습니다.<br>관련 문의는 고객센터로 연락해 주세요.
			<?php else: ?>
			아이디 또는 비밀번호를 다시 확인해 주세요.
			<?php endif; ?>
		</div>
		<button class="auth-modal-btn" onclick="closeModal()">확인</button>
	</div>
</div>
<?php endif; ?>

<script>
function closeModal() {
  document.getElementById('errorModal')?.classList.remove('show');
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
	const id = document.getElementById('mb_id').value.trim();
	const pw = document.getElementById('mb_password').value;
	let valid = true;

	const idGroup = document.getElementById('idGroup');
	const idErr   = document.getElementById('idError');
	const pwGroup = document.getElementById('pwGroup');
	const pwErr   = document.getElementById('pwError');

	idGroup.classList.remove('error'); idErr.style.display = 'none';
	pwGroup.classList.remove('error'); pwErr.style.display = 'none';

	if (id.length < 3) {
		idGroup.classList.add('error');
		idErr.textContent = '아이디는 최소 3자 이상이어야 합니다.';
		idErr.style.display = 'block';
		valid = false;
	}
	if (pw.length < 8) {
		pwGroup.classList.add('error');
		pwErr.textContent = '비밀번호는 최소 8자 이상이어야 합니다.';
		pwErr.style.display = 'block';
		valid = false;
	}
	if (!valid) e.preventDefault();
});
</script>
</body>
</html>
