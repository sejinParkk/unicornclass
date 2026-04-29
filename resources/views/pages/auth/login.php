<div class="auth-card">
	<div class="auth-logo">
		<img src="/assets/img/logo2.svg" alt="유니콘클래스">
	</div>

	<?php if (!empty($_GET['reset'])): ?>
	<div class="alert-success">비밀번호가 변경되었습니다. 새 비밀번호로 로그인해주세요.</div>
	<?php endif; ?>

	<!-- 소셜 버튼 (키 미설정으로 비활성) -->
	<div class="social-btns">
		<button class="btn-social btn_kakao" disabled title="준비 중">
			<img src="/assets/img/auth_sns_kakao.svg" alt="카카오 로그인">
			<strong>카카오로 3초만에 시작하기</strong>
			<span class="badge-soon">준비중</span>
		</button>
		<button class="btn-social btn_naver" disabled title="준비 중">
			<img src="/assets/img/auth_sns_naver.svg" alt="네이버 로그인">
			<strong>네이버로 3초만에 시작하기</strong>
			<span class="badge-soon">준비중</span>
		</button>
	</div>  

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
		<button type="submit" class="btn-next mgt24" id="loginBtn">로그인</button>
	</form>

	<div class="auth-links">
		<a href="/find-id">아이디 찾기</a>
		<a href="/find-password">비밀번호 찾기</a>
		<a href="/register">회원가입</a>
	</div>
</div>

<!-- 에러 모달 (JS로 동적 제어) -->
<div class="auth-modal-overlay" id="errorModal">
	<div class="auth-modal-card">
		<div class="auth-modal-title" id="modalTitle"></div>
		<div class="auth-modal-desc" id="modalDesc"></div>
		<button class="btn-next mgt24 auth-modal-btn" onclick="closeModal()">확인</button>
	</div>
</div>

<script>
const _loginErrorMessages = {
  wrong:    { title: '일치하는 정보가 없습니다',     desc: '아이디 또는 비밀번호를 다시 확인해 주세요.' },
  warn:     { title: '로그인 정보를 확인해 주세요',  desc: null },
  locked:   { title: '계정이 잠겼습니다',            desc: '로그인 5회 이상 실패로 계정이 잠겼습니다.<br>고객센터로 문의해 주세요.' },
  inactive: { title: '이용할 수 없는 계정입니다',    desc: '탈퇴 또는 정지된 계정입니다.<br>관련 문의는 고객센터로 연락해 주세요.' },
};

function closeModal() {
  document.getElementById('errorModal')?.classList.remove('show');
}

function _showLoginModal(errorType, failRemaining) {
  const m = _loginErrorMessages[errorType] || _loginErrorMessages.wrong;
  document.getElementById('modalTitle').textContent = m.title;
  const descEl = document.getElementById('modalDesc');
  if (errorType === 'warn') {
    descEl.innerHTML = '아이디 또는 비밀번호를 다시 확인해 주세요.<br>'
      + '<span style="color:#e53e3e;font-weight:600;">' + failRemaining + '회 더 실패 시 계정이 잠깁니다.</span>';
  } else {
    descEl.innerHTML = m.desc || '';
  }
  document.getElementById('errorModal').classList.add('show');
}

document.getElementById('loginForm').addEventListener('submit', async function(e) {
  e.preventDefault();

  const id = document.getElementById('mb_id').value.trim();
  const pw = document.getElementById('mb_password').value;

  const idGroup = document.getElementById('idGroup');
  const idErr   = document.getElementById('idError');
  const pwGroup = document.getElementById('pwGroup');
  const pwErr   = document.getElementById('pwError');
  idGroup.classList.remove('error'); idErr.style.display = 'none';
  pwGroup.classList.remove('error'); pwErr.style.display = 'none';

  let valid = true;
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
  if (!valid) return;

  const btn = document.getElementById('loginBtn');
  btn.disabled = true; btn.textContent = '로그인 중...';

  try {
    const res  = await fetch('/login', { method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await res.json();
    if (data.ok) {
      location.href = data.redirect || '/';
    } else {
      _showLoginModal(data.errorType, data.failRemaining ?? 0);
    }
  } catch {
    _showLoginModal('wrong');
  } finally {
    btn.disabled = false; btn.textContent = '로그인';
  }
});
</script>
