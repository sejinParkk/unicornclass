<?php
/**
 * 관리자 프로필
 * @var array  $admin
 * @var string $csrfToken
 * @var bool   $saved
 * @var bool   $pwChanged
 */
?>

<?php if ($saved): ?>
<div class="toast-msg toast-success">✓ 프로필이 저장되었습니다.</div>
<?php endif; ?>
<?php if ($pwChanged): ?>
<div class="toast-msg toast-success">✓ 비밀번호가 변경되었습니다.</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="toast-msg toast-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>
<?php if (isset($_GET['pw_error'])): ?>
<div class="toast-msg toast-error"><?= htmlspecialchars($_GET['pw_error']) ?></div>
<?php endif; ?>

<div class="form-layout ver2">
  <div>
	<!-- 계정 정보 요약 -->
	<div class="form-card">
		<h3>계정 정보</h3>
		<div class="meta-info">
			<div>
				<div class="label">로그인 아이디</div>
				<div><?= htmlspecialchars($admin['login_id']) ?></div>
			</div>
			<div>
				<div class="label">마지막 로그인</div>
				<div><?= $admin['last_login_at'] ? date('Y-m-d H:i', strtotime($admin['last_login_at'])) : '-' ?></div>
			</div>
			<div>
				<div class="label">계정 생성일</div>
				<div><?= date('Y-m-d', strtotime($admin['created_at'])) ?></div>
			</div>
			<div>
				<div class="label">상태</div>
				<div><?= $admin['is_active'] ? '활성' : '비활성' ?></div>
			</div>
		</div>
	</div>

	<!-- 정보 수정 -->
	<div class="form-card">
		<h3>기본 정보 수정</h3>
		<form id="adminProfileForm" method="POST" action="/admin/profile">
			<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
			<div class="form-group">
				<label>로그인 아이디</label>
				<input type="text" class="form-control" value="<?= htmlspecialchars($admin['login_id']) ?>" readonly>
				<div class="hint">아이디는 변경할 수 없습니다.</div>
			</div>
			<div class="form-group">
				<label>이름 <span style="color:#c0392b">*</span></label>
				<input type="text" name="name" class="form-control"
					   value="<?= htmlspecialchars($admin['name']) ?>" placeholder="관리자 이름">
				<div data-ajax-err="name" class="error-msg" style="display:none"></div>
			</div>
			<div class="form-group">
				<label>이메일</label>
				<input type="email" name="email" class="form-control"
					   value="<?= htmlspecialchars($admin['email'] ?? '') ?>" placeholder="admin@unicornclass.com">
				<div data-ajax-err="email" class="error-msg" style="display:none"></div>
			</div>
			<button type="submit" class="btn-save">저장</button>
		</form>
	</div>
	</div>

	<div>
	<!-- 비밀번호 변경 -->
	<div class="form-card">
		<h3>비밀번호 변경</h3>
		<form id="adminPwForm" method="POST" action="/admin/profile/password">
			<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
			<div class="form-group">
				<label>현재 비밀번호 <span style="color:#c0392b">*</span></label>
				<input type="password" name="current_password" class="form-control"
					   placeholder="현재 비밀번호 입력" autocomplete="current-password">
				<div data-ajax-err="current_password" class="error-msg" style="display:none"></div>
			</div>
			<div class="form-group">
				<label>새 비밀번호 <span style="color:#c0392b">*</span></label>
				<input type="password" name="new_password" class="form-control"
					   placeholder="8자 이상" autocomplete="new-password">
				<div data-ajax-err="new_password" class="error-msg" style="display:none"></div>
			</div>
			<div class="form-group">
				<label>새 비밀번호 확인 <span style="color:#c0392b">*</span></label>
				<input type="password" name="confirm_password" class="form-control"
					   placeholder="새 비밀번호 재입력" autocomplete="new-password">
				<div data-ajax-err="confirm_password" class="error-msg" style="display:none"></div>
			</div>
			<button type="submit" class="btn-save">비밀번호 변경</button>
		</form>
	</div>
	</div>
</div>
<script>
document.getElementById('adminProfileForm').addEventListener('submit', function(e) {
	e.preventDefault();
	ajaxSubmit(this, {
		onSuccess: function() {
			var toast = document.createElement('div');
			toast.className = 'toast-msg toast-success';
			toast.textContent = '✓ 프로필이 저장되었습니다.';
			document.querySelector('.form-layout').insertAdjacentElement('beforebegin', toast);
			setTimeout(function() { toast.remove(); }, 3000);
		}
	});
});
document.getElementById('adminPwForm').addEventListener('submit', function(e) {
	e.preventDefault();
	ajaxSubmit(this, {
		onSuccess: function() {
			var toast = document.createElement('div');
			toast.className = 'toast-msg toast-success';
			toast.textContent = '✓ 비밀번호가 변경되었습니다.';
			document.querySelector('.form-layout').insertAdjacentElement('beforebegin', toast);
			this.reset();
			setTimeout(function() { toast.remove(); }, 3000);
		}.bind(document.getElementById('adminPwForm'))
	});
});
</script>
